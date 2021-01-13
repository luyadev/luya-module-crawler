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
            [['description'], 'string'],
            [['content'], 'string', 'max' => 65535],
            [['last_indexed', 'crawled', 'status_code', 'is_dublication'], 'integer'],
            [['url', 'title'], 'string', 'max' => 200],
            [['language_info', 'content_hash'], 'string', 'max' => 80],
            [['url_found_on_page'], 'string', 'max' => 255],
            [['group'], 'string', 'max' => 120],
            [['url'], 'unique'],
        ];
    }
}
