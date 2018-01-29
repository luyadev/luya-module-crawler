<?php

namespace luya\crawler\models;

use luya\crawler\admin\Module;
use luya\helpers\StringHelper;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Temporary Builder Index Model.
 *
 * The Builder Index is used while the crawl process. After a success crawl for the given website, the whole BuilderIndex
 * will be synced into the {{luya\crawler\models\Index}} model.
 *
 * @property int $id
 * @property string $url
 * @property string $title
 * @property string $content
 * @property string $description
 * @property string $language_info
 * @property string $url_found_on_page
 * @property string $group
 * @property int $last_indexed
 * @property int $crawled
 * @property int $status_code
 * @property string $content_hash
 * @property int $is_dublication
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Builderindex extends NgRestModel
{
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'preparePageVariables']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'preparePageVariables']);
    }
    
    /**
     * Prepare the page variables like contant hash and if its dulication by content.
     */
    public function preparePageVariables()
    {
        $this->content_hash = md5($this->content);
        $this->is_dublication = self::find()->where(['content_hash' => $this->content_hash])->andWhere(['!=', 'url', $this->url])->exists();
    }

    public static function tableName()
    {
        return 'crawler_builder_index';
    }

    public function scenarios()
    {
        return [
            'restcreate' => ['url', 'content', 'title', 'language_info', 'url_found_on_page', 'group'],
            'restupdate' => ['url', 'content', 'title', 'language_info', 'url_found_on_page', 'group'],
            'default' => ['url', 'content', 'title', 'language_info', 'content_hash', 'is_dublication', 'url_found_on_page', 'group', 'description'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'url' => Module::t('builderindex_url'),
            'title' => Module::t('builderindex_title'),
            'language_info' => Module::t('builderindex_language_info'),
            'content' => Module::t('builderindex_content'),
            'url_found_on_page' => Module::t('builderindex_url_found'),
        ];
    }
    
    /* ngrest model properties */
    
    public function genericSearchFields()
    {
        return ['url', 'content', 'title', 'language_info'];
    }
    
    public static function ngRestApiEndpoint()
    {
        return 'api-crawler-builderindex';
    }
    
    public function ngRestAttributeTypes()
    {
        return [
            'url' => 'text',
            'title' => 'text',
            'language_info' => 'text',
            'url_found_on_page' => 'text',
            'content' => 'textarea',
        ];
    }
    
    public function ngRestConfig($config)
    {
        $this->ngRestConfigDefine($config, 'list', ['url', 'title', 'language_info', 'url_found_on_page']);
        $this->ngRestConfigDefine($config, ['create', 'update'], ['url', 'title', 'language_info', 'url_found_on_page', 'content']);
    
        return $config;
    }

    /* custom functions */

    /**
     * Whether an url is inexed or not (false = not in database or not yet crawler).
     * 
     * @param string $url
     * @return boolean
     */
    public static function isIndexed($url)
    {
        return self::find()->where(['url' => $url])->select(['crawled'])->scalar();
    }

    public static function findUrl($url)
    {
        return self::findOne(['url' => $url]);
    }

    /**
     * Add a given page to the index with status: uncrawled.
     * 
     * If there url exists already in the index, false is returned.
     * 
     * @param string $url
     * @param string $title
     * @param string $urlFoundOnPage
     * @return boolean
     */
    public static function addToIndex($url, $title = null, $urlFoundOnPage = null)
    {
        $model = self::find()->where(['url' => $url])->exists();

        if ($model) {
            return false;
        }

        $model = new self();
        $model->url = $url;
        $model->title = StringHelper::truncate($title, 197);
        $model->url_found_on_page = $urlFoundOnPage;
        $model->crawled = false;

        return $model->save(false);
    }
}
