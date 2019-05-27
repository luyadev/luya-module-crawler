<?php

namespace luya\crawler\models;

use luya\crawler\admin\Module;
use luya\helpers\StringHelper;
use luya\admin\ngrest\base\NgRestModel;
use yii\db\ActiveRecord;

/**
 * Temporary Builder Index Model.
 *
 * The Builder Index is used while the crawl process. After a success crawl for the given website, the whole BuilderIndex
 * will be synced into the {{luya\crawler\models\Index}} model.
 *
 * @property int $id
 * @property string $url
 * @property string $content
 * @property string $title
 * @property int $last_indexed
 * @property string $language_info
 * @property int $crawled
 * @property int $status_code
 * @property string $content_hash
 * @property int $is_dublication
 * @property string $url_found_on_page
 * @property string $group
 * @property string $description
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Builderindex extends ActiveRecord
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crawler_builder_index';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['content', 'description'], 'string'],
            [['last_indexed', 'crawled', 'status_code', 'is_dublication'], 'integer'],
            [['url', 'title'], 'string', 'max' => 200],
            [['language_info', 'content_hash'], 'string', 'max' => 80],
            [['url_found_on_page'], 'string', 'max' => 255],
            [['group'], 'string', 'max' => 120],
            [['url'], 'unique'],
        ];
    }

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

    /**
     * Find a crawler index entry based on the url.
     * 
     * @param string $url
     * @return \luya\crawler\models\Builderindex|boolean
     */
    public static function findUrl($url)
    {
        return self::find()->where(['url' => $url])->limit(1)->one();
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

        return $model->save();
    }
}
