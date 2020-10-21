<?php

use yii\db\Migration;

/**
 * Class m180129_144611_add_index_keys
 */
class m201021_085112_builder_indexers extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('crawler_builder_runtime_url', [
            'url' => $this->text()->notNull(),
        ]);

        $this->createIndex('url', 'crawler_builder_runtime_url', ['url']);

        $this->createTable('crawler_builder_runtime_checksum', [
            'checksum' => $this->string(64)->notNull(),
        ]);

        $this->createIndex('checksum', 'crawler_builder_runtime_checksum', ['checksum']);

        $this->createTable('crawler_builder_runtime_queue', [
            'url' => $this->string(),
            'referrer_url' => $this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('crawler_builder_runtime_url');
        $this->dropTable('crawler_builder_runtime_checksum');
        $this->dropTable('crawler_builder_runtime_queue');
    }
}
