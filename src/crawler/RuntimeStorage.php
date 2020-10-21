<?php

namespace luya\crawler\crawler;

use luya\helpers\ArrayHelper;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Interfaces\StorageInterface;
use Nadar\Crawler\QueueItem;
use Yii;
use yii\db\Query;

class RuntimeStorage implements StorageInterface
{
    public $urlTable = 'crawler_builder_runtime_url';

    public $checksumTable = 'crawler_builder_runtime_checksum';

    public $queueTable = 'crawler_builder_runtime_queue';

    public function onSetup(Crawler $crawler)
    {
        Yii::$app->db->createCommand()->truncateTable($this->urlTable)->execute();
        Yii::$app->db->createCommand()->truncateTable($this->checksumTable)->execute();
        Yii::$app->db->createCommand()->truncateTable($this->queueTable)->execute();
    }

    public function onEnd(Crawler $crawler)
    {
        
    }

    public function isUrlDone($url): bool
    {
        return (new Query)->from($this->urlTable)->where(['url' => $url])->exists();
    }

    public function markUrlAsDone($url)
    {
        return Yii::$app->db->createCommand()->insert($this->urlTable, ['url' => $url])->execute();
    }

    public function isChecksumDone($checksum): bool
    {
        return (new Query)->from($this->checksumTable)->where(['checksum' => $checksum])->exists();
    }

    public function markChecksumAsDone($checksum)
    {
        return Yii::$app->db->createCommand()->insert($this->checksumTable, ['checksum' => $checksum])->execute();
    }

    public function pushQueue(QueueItem $queueItem)
    {
        return Yii::$app->db->createCommand()->insert($this->queueTable, [
            'url' => $queueItem->url,
            'referrer_url' => $queueItem->referrerUrl,
        ])->execute();
    }

    public function retrieveQueue($amount): array
    {
        $query = (new Query)
            ->from($this->queueTable)
            ->select(['url', 'referrer_url'])
            ->limit($amount)
            ->orderBy(['url' => SORT_ASC]);

        $items = $query->all();

        
        array_walk($items, function(&$item) {
            $item = new QueueItem($item['url'], $item['referrer_url']);
        });

        $urls = ArrayHelper::getColumn($items, 'url');

        Yii::$app->db->createCommand()->delete($this->queueTable, ['in', 'url', $urls])->execute();

        unset($urls);

        return $items;
    }
}