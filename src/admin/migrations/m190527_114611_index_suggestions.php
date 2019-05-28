<?php

use yii\db\Migration;

/**
 * Class m180129_144611_add_index_keys
 */
class m190527_114611_index_suggestions extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('crawler_searchdata', 'didyoumean_suggestion_count', $this->integer()->defaultValue(0));
        $this->addColumn('crawler_searchdata', 'resolved_by_didyoumean_searchdata_id', $this->integer());
        $this->createIndex('language_results_query', 'crawler_searchdata', ['language', 'results', 'query']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('crawler_searchdata', 'didyoumean_suggestion_count');
        $this->dropColumn('crawler_searchdata', 'resolved_by_didyoumean_searchdata_id');
        $this->dropIndex('language_results_query', 'crawler_searchdata');
    }
}
