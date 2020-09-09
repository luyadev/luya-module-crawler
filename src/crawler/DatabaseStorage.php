<?php

namespace luya\crawler\crawler;

use Yii;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Interfaces\StorageInterface;
use Nadar\Crawler\QueueItem;

class DatabaseStorage implements StorageInterface
{
    public $queue = [];

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
                $index->save();

                unset($index, $builderIndex);
            }
        }

        unset($batch);
    }

    public function isUrlDone($url): bool
    {
        return Builderindex::find()->where(['url' => $url])->exists();
    }

    public function markUrlAsDone($url)
    {
        Yii::$app->db->createCommand()->insert(Builderindex::tableName(), ['url' => $url])->execute();
    }

    public function isChecksumDone($checksum): bool
    {
        return Builderindex::find()->where(['content_hash' => $checksum])->exists();
    }

    public function markChecksumAsDone($checksum)
    {
        Yii::$app->db->createCommand()->insert(Builderindex::tableName(), ['content_hash' => $checksum])->execute();
    }

    public function pushQueue(QueueItem $queueItem)
    {
        $this->queue[] = $queueItem;
    }

    /**
     * Must return an array with QueueItem objects and the retrieved items MUST be deleted from the queue!
     * 
     * + Must return an array with QueueItems
     * + The runtime stack integrator retrieveQueue() must take care of empting the queue
     * + empty if the queue is empty, an empty array will be returned. so the crawler knows to finish the crawler process.
     *
     * @param integer $amount
     * @return array
     */
    public function retrieveQueue($amount): array
    {
        return array_splice($this->queue, 0, $amount);
    }
}