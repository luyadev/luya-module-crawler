<?php

use yii\db\Migration;

/**
 * Class m180129_144611_add_index_keys
 */
class m180129_144611_add_index_keys extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
    	// index
		$this->createIndex('crawler_index_url_url-index', 'crawler_index', ['url']);
		// builder index
		$this->createIndex('crawler_builder_index-url_index', 'crawler_builder_index', ['url']);
		$this->createIndex('crawler_builder_index-crawled_index', 'crawler_builder_index', ['crawled']);
		$this->createIndex('crawler_builder_index-is_dublication_index', 'crawler_builder_index', ['is_dublication']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    	// index
    	$this->dropIndex('crawler_index_url_url-index', 'crawler_index');
    	// builder index
    	$this->dropIndex('crawler_builder_index-url_index', 'crawler_builder_index');
    	$this->dropIndex('crawler_builder_index-crawled_index', 'crawler_builder_index');
    	$this->dropIndex('crawler_builder_index-is_dublication_index', 'crawler_builder_index');
    }
}
