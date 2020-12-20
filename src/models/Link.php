<?php

namespace luya\crawler\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use yii\behaviors\TimestampBehavior;
use Curl\Curl;
use luya\crawler\frontend\Module as FrontendModule;
use luya\crawler\admin\buttons\DoneButton;
use luya\crawler\admin\Module;
use Nadar\Crawler\Url;
use yii\db\BatchQueryResult;

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
            'url' => ['url', 'encoding' => false],
            'title' => 'text',
            'url_found_on_page' => ['text', 'hideInList' => true, 'encoding' => false],
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
        /*
        $curl = new Curl();
        $curl->setOpt(CURLOPT_TIMEOUT, 5);
        //$curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
        $curl->setUserAgent(FrontendModule::CRAWLER_USER_AGENT);
        $curl->get($url);
        $status = $curl->http_status_code;
        $curl->close();
        unset($curl);
        gc_collect_cycles();
        return $status;
        */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // This changes the request method to HEAD
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5); // connect timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // curl timeout

        $exec = curl_exec($ch);
        
        if ($exec) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        } else {
            $status = -1;
        }

        curl_close($ch);
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
        $urlObject = new Url($url);
        $urlObject->encode = true;

        // if the url is rerlative, it might be an url on the same site, therfore merge with base url
        if ($urlObject->isRelative()) {
            $urlObject->merge(new Url($urlOnPage));
        }

        if (!$urlObject->isValid()) {
            return false;
        }

        $url = $urlObject->getNormalized();

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
     *
     * @return array An array with link as the key and value is the status code.
     */
    public static function updateLinkStatus()
    {
        $log = [];
        foreach (self::getAllUrlsBatch() as $batch) {
            foreach ($batch as $link) {
                $status = self::responseStatus($link['url']);
                $log[] = [$link['url'], $status];
                self::updateUrlStatus($link['url'], $status);
            }
        }
        return $log;
    }

    /**
     * Get the batch with all urls
     *
     * @return BatchQueryResult
     */
    public static function getAllUrlsBatch()
    {
        return self::find()->select(['url'])->asArray()->distinct()->batch();
    }

    /**
     * Update the status of a given url
     *
     * @param string $url
     * @param integer $status
     * @return int the number of rows updated
     */
    public static function updateUrlStatus($url, $status)
    {
        self::updateAll(['response_status' => $status, 'updated_at' => time()], ['url' => $url]);
    }
}
