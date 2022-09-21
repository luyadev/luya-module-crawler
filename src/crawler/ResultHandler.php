<?php

namespace luya\crawler\crawler;

use luya\crawler\frontend\commands\CrawlController;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use luya\crawler\models\Link;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Interfaces\HandlerInterface;
use Nadar\Crawler\Result;
use Yii;
use yii\base\Event;
use yii\console\Exception;
use yii\helpers\Console;

/**
 * Handle the Crawler Results
 *
 * Add new pages to the index and sync the production index from builder index on finish.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.0.0
 */
class ResultHandler implements HandlerInterface
{
    const EVENT_BEFORE_PROCESS = 'beforeProcess';
    
    const EVENT_AFTER_INDEX = 'afterIndex';

    /**
     * @var CrawlController
     */
    protected $controller;

    /**
     * Constructor
     *
     * @param CrawlController $controller
     */
    public function __construct(CrawlController $controller)
    {
        $this->controller = $controller;
    }
    
    /**
    * {@inheritDoc}
    */
    public function onSetup(Crawler $crawler)
    {
        Builderindex::deleteAll();
    }

    /**
     * {@inheritDoc}
     */
    public function afterRun(Result $result)
    {
        $url = $result->url->getNormalized();
        
        $index = Builderindex::findOne(['url' => $url]);

        if (!$index) {
            $index = new Builderindex();
            $index->url = $url;
        }

        $content = $result->content;
        if (!empty($result->keywords)) {
            $content.= ' ' . $result->keywords;
        }
        
        $index->content = $content;
        $index->title = $result->title;
        $index->description = $result->description;
        $index->language_info = $result->language;
        $index->url_found_on_page = $result->refererUrl->getNormalized();
        $index->group = $result->group;

        $index->save();
        unset($index, $content);
        
        if ($this->controller->linkcheck) {
            foreach ($result->parserResult->links as $linkUrl => $value) {
                Link::add($linkUrl, $value, $url);
            }
        }
    }

    /**
    * {@inheritDoc}
    */
    public function onEnd(Crawler $crawler)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $keepIndexIds = [];
            $currentTotal = (int) Index::find()->count();
            $total = (int) Builderindex::find()->count();

            if (!$this->controller->purging && ($currentTotal > 0 && $total == 0)) {
                throw new Exception("The old index contained {$currentTotal} while the new index is empty. Possible misconfiguration or error while crawling the website. The force an empty index us --purging=1");
            }

            $this->controller->module->trigger(self::EVENT_BEFORE_PROCESS, new Event());

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

            $this->controller->module->trigger(self::EVENT_AFTER_INDEX, new Event());

            $transaction->commit();
            unset($batch);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
