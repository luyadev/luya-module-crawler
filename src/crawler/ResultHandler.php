<?php

namespace luya\crawler\crawler;

use luya\crawler\frontend\commands\CrawlController;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use luya\crawler\models\Link;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Interfaces\HandlerInterface;
use Nadar\Crawler\Result;
use yii\helpers\Console;

class ResultHandler implements HandlerInterface
{
    protected $controller;

    public function __construct(CrawlController $controller)
    {
        $this->controller = $controller;    
    }

    public function afterRun(Result $result)
    {
        $index = Builderindex::findOne(['url' => $result->url->getNormalized()]);

        if (!$index) {
            $index = new Builderindex();
            $index->url = $result->url->getNormalized();
        }

        $index->content = $result->content;
        $index->title = $result->title;
        $index->description = $result->description;
        $index->language_info = $result->language;
        $index->url_found_on_page = $result->refererUrl->getNormalized();
        $index->group = $result->group;

        $index->save();
        unset($index);
        
        if ($this->controller->linkcheck) {
            foreach ($result->parserResult->links as $url => $value) {
                Link::add($url, $value, $result->url->getNormalized());
            }
        }
    }

    public function onSetup(Crawler $crawler)
    {
        Builderindex::deleteAll();
    }

    public function onEnd(Crawler $crawler)
    {
        $keepIndexIds = [];
        
        $total = (int) Builderindex::find()->count();
        $i = 0;
        if ($this->controller->verbose) {
            Console::startProgress(0, $total, 'synchronize index: ', false);    
        }
        foreach (Builderindex::find()->batch() as $batch) {
            foreach ($batch as $builderIndex) {
                $index = Index::findOne(['url' => $builderIndex->url]);

                if (!$index) {
                    $index = new Index();
                    $index->added_to_index = time();
                }

                $index->url = $builderIndex->url;
                $index->title = $builderIndex->title;
                $index->description = $builderIndex->description;
                $index->content = $builderIndex->content;
                $index->language_info = $builderIndex->language_info;
                $index->last_update = time();
                $index->url_found_on_page = $builderIndex->url_found_on_page;
                $index->group = $builderIndex->group;
                $index->save();

                $keepIndexIds[] = $index->id;
                unset($index, $builderIndex);
                $i++;

                if ($this->controller->verbose) {
                    Console::updateProgress($i, $total);
                }
            }
        }

        Index::deleteAll(['not in', 'id', $keepIndexIds]);

        if ($this->controller->verbose) {
            Console::endProgress("done." . PHP_EOL);
        }

        unset($batch);
    }
}