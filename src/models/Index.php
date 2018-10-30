<?php

namespace luya\crawler\models;

use luya\admin\ngrest\base\NgRestModel;
use luya\crawler\admin\Module;
use Nadar\Stemming\Stemm;
use yii\db\Expression;
use luya\helpers\StringHelper;
use luya\helpers\ArrayHelper;

/**
 * The Crawler Index Model.
 *
 * This table contains the crawler content for a given Website.
 *
 * @property integer $id
 * @property string $url
 * @property string $title
 * @property string $content
 * @property string $description
 * @property string $language_info
 * @property string $url_found_on_page
 * @property string $group
 * @property integer $added_to_index
 * @property integer $last_update
 * @property string $clickUrl
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Index extends NgRestModel
{
    public static $counter = 0;
    
    public static $searchDataId = 0;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crawler_index';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['content', 'description'], 'string'],
            [['added_to_index', 'last_update'], 'integer'],
            [['url', 'title'], 'string', 'max' => 200],
            [['language_info'], 'string', 'max' => 80],
            [['url_found_on_page'], 'string', 'max' => 255],
            [['group'], 'string', 'max' => 120],
            [['url'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'url' => Module::t('index_url'),
            'title' => Module::t('index_title'),
            'language_info' => Module::t('index_language_info'),
            'content' => Module::t('index_content'),
            'url_found_on_page' => Module::t('index_url_found'),
            'added_to_index' => ' add to index on',
            'last_update' => 'last update'
        ];
    }
    
    /**
     * Search by general Like statement returning ActiveRecords.
     *
     * @param string $query
     * @param string $languageInfo
     * @return \yii\db\ActiveRecord
     */
    public static function flatSearchByQuery($query, $languageInfo)
    {
        $query = static::encodeQuery($query);
    
        $query = Stemm::stemPhrase($query, $languageInfo);
        
        if (strlen($query) < 1) {
            return [];
        }
    
        $q = self::find()->where(['like', 'content', $query]);
        $q->orWhere(['like', 'description', $query]);
        $q->orWhere(['like', 'title', $query]);
        if (!empty($languageInfo)) {
            $q->andWhere(['language_info' => $languageInfo]);
        }
        $result = $q->all();
    
        $searchData = new Searchdata();
        $searchData->detachBehavior('LogBehavior');
        $searchData->attributes = [
            'query' => $query,
            'results' => count($result),
            'timestamp' => time(),
            'language' => $languageInfo,
        ];
        $searchData->save();
    
        return $result;
    }
    
    /**
     * Smart search Returning all ActiveRecords.
     *
     * @param string $query
     * @param string $languageInfo
     * @param string $returnQuery
     * @return \yii\db\ActiveRecord
     */
    public static function searchByQuery($query, $languageInfo)
    {
        if (strlen($query) < 1) {
            return [];
        }
        
        $activeQuery = self::activeQuerySearch($query, $languageInfo);
        
        $result = $activeQuery->all();
        
        $searchData = new Searchdata();
        $searchData->detachBehavior('LogBehavior');
        $searchData->attributes = [
            'query' => $query,
            'results' => count($result),
            'timestamp' => time(),
            'language' => $languageInfo,
        ];
        $searchData->save();
        
        return $result;
    }
    
    /**
     * Smart search by a query returnin the ActiveQuery instance.
     *
     * @param string $query
     * @param string $languageInfo
     * @param string $returnQuery
     * @return \yii\db\ActiveQuery
     */
    public static function activeQuerySearch($query, $languageInfo)
    {
        $query = static::encodeQuery($query);
        
        $parts = explode(" ", $query);
        
        $index = [];
        foreach ($parts as $word) {
            if (empty($word)) {
                continue;
            }
            
            $word = Stemm::stem($word, $languageInfo);
            $q = self::find()->select(['id', 'url', 'title', 'content']);
            $q->where([
                'or',
                ['like', 'content', $word],
                ['like', 'description', $word],
                ['like', 'title', $word],
            ]);
            if (!empty($languageInfo)) {
                $q->andWhere(['language_info' => $languageInfo]);
            }
            $data = $q->asArray()->indexBy('id')->all();
        
            static::indexer($word, $data, $index);
        }
        
        ArrayHelper::multisort($index, ['urlwordpos', 'title'], [SORT_DESC, SORT_ASC]);

        $ids = [];
        foreach ($index as $row) {
            $ids[] = $row['id'];
        }

        $activeQuery = self::find()->where(['in', 'id', $ids]);
        if (!empty($ids)) {
            $activeQuery->orderBy(new Expression('FIELD (id, ' . implode(', ', $ids) . ')'));
        }
        
        return $activeQuery;
    }

    
    private static $_midImportant = 500;
    
    private static $_unImportant = 1000;

    /**
     * Find a position for a given index item and keyword.
     * 
     * 1. Generate the index
     * 2. If multiple words, ensure the word also existing on the current index otherwise unset.
     * 
     * @param array $results
     * @param array $index The index
     */
    private static function indexer($keyword, array $results, &$index)
    {
        // its only empty when the indexer runs for the first word
        if (empty($index)) {
            foreach ($results as $id => $v) {
                $item = $v;
                $item['urlwordpos'] = static::evalPosition($v, $keyword);
                $index[$id] = $item;
            }
        } else {
            // now the indexer is running for the next word for the whole index
            foreach ($index as $id => $v) {
                // If the current results array does not provide the same page id, remove as its not found on the same page
                if (!array_key_exists($id, $results)) {
                    unset($index[$id]);
                } else {
                    // if there is already an index, check if the the new position for this word is better:
                    $newPos = static::evalPosition($v, $keyword);

                    if ($newPos > $index[$id]['urlwordpos']) {
                        $index[$id]['urlwordpos'] = $newPos;
                    }
                }
            }
        }
    }

    /**
     * Get best word distance.
     *
     * @param array $words
     * @param string $keyword
     * @return integer
     */
    private static function getBestWordDistance(array $words, $keyword)
    {
        $i = 0;
        foreach ($words as $word) {
            $v = 0;
            similar_text($word, $keyword, $v);
            if ($v > $i) {
                $i = $v;
            }
        }

        return $i;
    }
    
    /**
     * Find a position index for a given key word inside an item.
     * 
     * NEW: Bigger is better!
     *
     * @param array $item
     * @param [type] $keyword
     * @return void
     */
    private static function evalPosition(array $item, $keyword)
    {
        $keyword = strtolower($keyword);
        $url = strtolower(parse_url($item['url'], PHP_URL_PATH));
        $posInUrl = self::getBestWordDistance(explode("/", $url), $keyword);
        $posInTitle = self::getBestWordDistance(explode(" " , strtolower($item['title'])), $keyword);
        $partialWordCount = substr_count(strtolower($item['content']), $keyword);
        $exactWordCount = preg_match_all('/\b'. preg_quote($keyword) .'\b/', strtolower($item['content']));


        $partialWordCount = $partialWordCount / 5;
        $exactWordCount = $exactWordCount / 5;

        return $posInUrl + $posInTitle + $partialWordCount + $exactWordCount;
    }
    
    /**
     * Encode the input query.
     *
     * @param string $query
     * @return string
     */
    public static function encodeQuery($query)
    {
        return trim(htmlentities($query, ENT_QUOTES));
    }
    

    /**
     * Generate preview from the search word and the corresponding cut amount.
     *
     * @param string $word The word too lookup in the `$content` variable.
     * @param number $cutAmount The amount of words on the left and right side of the word.
     * @return mixed
     */
    public function preview($word, $cutAmount = 150)
    {
        $cut = StringHelper::truncateMiddle($this->content, $word, $cutAmount);
        
        return StringHelper::highlightWord($cut, $word, '<span style="background-color:#FFEBD1; color:black;">%s</span>');
    }

    /**
     * Cut the string around the given word.
     *
     * @param string $word
     * @param string $context
     * @param number $truncateAmount
     * @return string
     */
    public function cut($word, $context, $truncateAmount = 150)
    {
        return StringHelper::truncateMiddle($context, $word, $truncateAmount);
    }

    /**
     * Highlight the given word.
     *
     * @param string $word
     * @param string $text
     * @param string $sheme
     * @return mixed
     */
    public function highlight($word, $text, $sheme = '<span style="background-color:#FFEBD1; color:black;">%s</span>')
    {
        return StringHelper::highlightWord($text, $word, $sheme);
    }
    
    /**
     * @inheritdoc
     */
    public function genericSearchFields()
    {
        return ['url', 'content', 'title'];
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-crawler-index';
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'url' => 'text',
            'title' => 'text',
            'language_info' => 'text',
            'url_found_on_page' => 'text',
            'content' => ['textarea', 'encoding' => false],
            'last_update' => 'datetime',
            'added_to_index' => 'datetime',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestConfig($config)
    {
        $this->ngRestConfigDefine($config, 'list', ['title', 'url', 'language_info', 'last_update', 'added_to_index']);
        $this->ngRestConfigDefine($config, ['create', 'update'], ['url', 'title', 'language_info', 'url_found_on_page', 'content', 'last_update', 'added_to_index']);
        return $config;
    }
}
