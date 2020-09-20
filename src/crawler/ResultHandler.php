<?php

namespace luya\crawler\crawler;

use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use luya\crawler\models\Link;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Interfaces\HandlerInterface;
use Nadar\Crawler\Result;

class ResultHandler implements HandlerInterface
{
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
        
        foreach ($result->parserResult->links as $link) {
            // @TODO remove unknown title... replace with acutall link
            Link::add($link, 'unknown', $result->url->getNormalized());
        }

    }

    public function onSetup(Crawler $crawler)
    {
        Builderindex::deleteAll();
    }

    public function onEnd(Crawler $crawler)
    {
        Index::deleteAll();

        foreach (Builderindex::find()->batch() as $batch) {
            foreach ($batch as $builderIndex) {
                $index = new Index();
                $index->url = $builderIndex->url;
                $index->title = $builderIndex->title;
                $index->description = $builderIndex->description;
                $index->content = $builderIndex->content;
                $index->language_info = $builderIndex->language_info;
                $index->added_to_index = time();
                $index->last_update = time();
                $index->url_found_on_page = $builderIndex->url_found_on_page;
                $index->group = $builderIndex->group;
                $index->save();

                unset($index, $builderIndex);
            }
        }

        unset($batch);
    }
}