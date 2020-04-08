<?php

use yii\db\Migration;

/**
 * Class m180129_144611_add_index_keys
 */
class m200409_101814_search_indexes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex('language_info' , 'crawler_index', ['language_info']);
        $this->createIndex('group' , 'crawler_index', ['group']);

        if ($this->db->driverName == 'mysql') {
            $time = $this->beginCommand('create fulltext index on content');
            $this->db->createCommand('CREATE FULLTEXT INDEX content ON crawler_index ( content );')->execute();
            $this->endCommand($time);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('language_info' , 'crawler_index');
        $this->dropIndex('group' , 'crawler_index');

        if ($this->db->driverName == 'mysql') {
            $this->dropIndex('content' , 'crawler_index');
        }
    }
}
