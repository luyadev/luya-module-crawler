<?php

namespace luya\crawler\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use yii\behaviors\TimestampBehavior;
use Curl\Curl;
use luya\crawler\frontend\Module as FrontendModule;
use luya\crawler\admin\buttons\DoneButton;
use luya\crawler\admin\Module;

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
            'id' => Module::t('ID'),
            'url' => Module::t('link_url'),
            'url_found_on_page' => Module::t('link_url_found_on_page'),
            'title' => Module::t('link_title'),
            'response_status' => Module::t('link_response_status'),
            'created_at' => Module::t('link_created_at'),
            'updated_at' => Module::t('link_updated_at'),
            'is_ignored' => Module::t('link_is_ignored'),
            'cleanFoundUrl' => Module::t('link_url_found_on_page'),
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

    public function ngRestGroupByField()
    {
        return 'cleanFoundUrl';
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'url' => 'url',
            'title' => 'text',
            'url_found_on_page' => ['text', 'hideInList' => true],
            'response_status' => 'number',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_ignored' => 'toggleStatus',
        ];
    }

    public function ngRestExtraAttributeTypes()
    {
        return [
            'cleanFoundUrl' => ['url', 'sortField' => 'url_found_on_page', 'linkAttribute' => 'url_found_on_page'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', [ 'cleanFoundUrl', 'url_found_on_page', 'url', 'title', 'response_status']],
            ['delete', false],
        ];
    }

    public function ngRestActiveButtons()
    {
        return [
            ['class' => DoneButton::class],
        ];
    }

    public function getCleanFoundUrl()
    {
        $parse = parse_url($this->url_found_on_page);

        $query = isset($parse['query']) ? $parse['query'] : null;

        return $parse['path'] . $query;
    }

    /**
     * @inheritDoc
     */
    public static function ngRestFind()
    {
        return parent::ngRestFind()->andWhere(['>=', 'response_status', 400]);
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
        $curl->setUserAgent(FrontendModule::CRAWLER_USER_AGENT);
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
        foreach (self::find()->select(['url'])->asArray()->distinct()->batch() as $batch) {
            foreach ($batch as $link) {
                $status = self::responseStatus($link['url']);
                self::updateAll(['response_status' => $status], ['url' => $link['url']]);
            }
        }
    }
}
