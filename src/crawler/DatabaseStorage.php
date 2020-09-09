<?php

namespace luya\crawler\crawler;

use Yii;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Storage\ArrayStorage;

class DatabaseStorage extends ArrayStorage
{
    public function onSetup(Crawler $crawler)
    {
        parent::onSetup($crawler);
        Builderindex::deleteAll();
    }

    public function onEnd(Crawler $crawler)
    {
        parent::onEnd($crawler);
        Index::deleteAll();

        foreach (Builderindex::find()->batch() as $batch) {
            foreach ($batch as $builderIndex) {
                $index = new Index();
                $index->url = $builderIndex->url;
                $index->title = $builderIndex->title;
                $index->description = $builderIndex->description;
                $index->content = $builderIndex->content;
                $index->save();

                unset($index, $builderIndex);
            }
        }

        unset($batch);
    }
}