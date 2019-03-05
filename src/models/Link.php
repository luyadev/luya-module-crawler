<?php

namespace luya\crawler\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use yii\behaviors\TimestampBehavior;
use Curl\Curl;
use luya\crawler\frontend\Module;

/**
 * Link.
 * 
 * File has been created with `crud/create` command. 
 *
 * @property integer $id
 * @property string $url
 * @property string $url_found_on_page
 * @property string $title
 * @property integer $response_status
 * @property integer $created_at
 * @property integer $updated_at
 * @property tinyint $is_ignored
 */
class Link extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crawler_link';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-crawler-link';
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = TimestampBehavior::class;
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'Url'),
            'url_found_on_page' => Yii::t('app', 'Url Found On Page'),
            'title' => Yii::t('app', 'Title'),
            'response_status' => Yii::t('app', 'Response Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'is_ignored' => Yii::t('app', 'Is Ignored'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'url_found_on_page'], 'required'],
            [['response_status', 'created_at', 'updated_at', 'is_ignored'], 'integer'],
            [['url', 'url_found_on_page', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'url' => 'text',
            'url_found_on_page' => 'text',
            'title' => 'text',
            'response_status' => 'number',
            'created_at' => 'number',
            'updated_at' => 'number',
            'is_ignored' => 'number',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['url', 'url_found_on_page', 'title', 'response_status', 'created_at', 'updated_at', 'is_ignored']],
            [['create', 'update'], ['url', 'url_found_on_page', 'title', 'response_status', 'created_at', 'updated_at', 'is_ignored']],
            ['delete', false],
        ];
    }

    /**
     * Get the response status for the given link.
     * 
     * @param string $link
     * @return integer 
     */
    public static function responseStatus($url)
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_TIMEOUT, 3);
        $curl->setUserAgent(Module::CRAWLER_USER_AGENT);
        $curl->get($url);
        $status =  $curl->http_status_code;
        $curl->close();
        unset($curl);

        return $status;
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

    /**
     * Short hand method to cleanup all links which are old then the given time
     */
    public static function cleanup($time)
    {
        return self::deleteAll([
            'and',
            ['<', 'updated_at', $time],
        ]);
    }

    /**
     * Update the status for all links on the given url page.
     */
    public static function updateLinkStatus()
    {
        foreach (self::find()->select(['url'])->asArray()->distinct()->all() as $link) {
            $status = self::responseStatus($link['url']);
            self::updateAll(['response_status' => $status], ['url' => $link['url']]);
        }
    }
}