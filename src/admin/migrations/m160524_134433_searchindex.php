<?php

use yii\db\Migration;

class m160524_134433_searchindex extends Migration
{
    public function safeUp()
    {
        $this->createTable('crawler_searchdata', [
            'id' => 'pk',
            'query' => $this->string(120)->notNull(),
            'results' => $this->integer()->defaultValue(0),
            'timestamp' => $this->integer()->notNull(),
            'language' => $this->string(12),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('crawler_searchdata');
    }
}
