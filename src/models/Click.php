<?php

namespace luya\crawler\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "crawler_click".
 *
 * @property int $id
 * @property int $searchdata_id
 * @property int $position
 * @property int $index_id
 * @property int $timestamp
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.1
 */
class Click extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crawler_click';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchdata_id', 'position', 'index_id', 'timestamp'], 'required'],
            [['searchdata_id', 'position', 'index_id', 'timestamp'], 'integer'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'searchdata_id' => 'Searchdata ID',
            'position' => 'Position',
            'index_id' => 'Index ID',
            'timestamp' => 'Timestamp',
        ];
    }
}
