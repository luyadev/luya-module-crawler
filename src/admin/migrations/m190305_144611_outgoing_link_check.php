<?php

use yii\db\Migration;

/**
 * Class m180129_144611_add_index_keys
 */
class m190305_144611_outgoing_link_check extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('crawler_link', [
            'id' => $this->primaryKey(),
            'url' => $this->string()->notNull(),
            'url_found_on_page' => $this->string()->notNull(),
            'title' => $this->string(),
            'response_status' => $this->integer(),
            'created_at' => $this->integer(),
            'update_at' => $this->integer(),
            'is_ignored' => $this->boolean(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('crawler_links');
    }
}
