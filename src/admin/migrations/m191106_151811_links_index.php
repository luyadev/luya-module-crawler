<?php

use yii\db\Migration;

/**
 * Class m180129_144611_add_index_keys
 */
class m191106_151811_links_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('url_and_url_found_on_page', 'crawler_link', ['url', 'url_found_on_page']);
        $this->createIndex('url', 'crawler_link', ['url']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('url_and_url_found_on_page', 'crawler_link');
        $this->dropIndex('url', 'crawler_link');
    }
}
