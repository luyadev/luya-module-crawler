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
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('crawler_link');
    }
}
