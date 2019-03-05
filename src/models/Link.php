<?php

namespace luya\crawler\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "crawler_link".
 *
 * @property int $id
 * @property string $url
 * @property string $url_found_on_page
 * @property string $title
 * @property int $response_status
 * @property int $created_at
 * @property int $update_at
 * @property int $is_ignored
 */
class Link extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crawler_link';
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = TimestampBehavior::class;
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url', 'url_found_on_page'], 'required'],
            [['response_status', 'created_at', 'update_at', 'is_ignored'], 'integer'],
            [['url', 'url_found_on_page', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'url_found_on_page' => 'Url Found On Page',
            'title' => 'Title',
            'response_status' => 'Response Status',
            'created_at' => 'Created At',
            'update_at' => 'Update At',
            'is_ignored' => 'Is Ignored',
        ];
    }

    /**
     * Short hand to add new page which checks if the link also exists on the given page.
     * 
     * @param $url string
     * @param $title string
     * @param $urlOnPage string
     */
    public static function add($url, $title, $urlOnPage)
    {
        $model = self::find()->where(['url' => $url, 'url_found_on_page' => $urlOnPage])->one();

        if ($model) {
            $model->title = $title;
        } else {
            $model = new self;
            $model->title = $title;
            $model->url = $url;
            $model->url_found_on_page = $urlOnPage;
        }        

        return $model->save();
    }
}
