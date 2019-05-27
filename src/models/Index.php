<?php

namespace luya\crawler\models;

use luya\admin\ngrest\base\NgRestModel;
use luya\crawler\admin\Module;
use Nadar\Stemming\Stemm;
use yii\db\Expression;
use luya\helpers\StringHelper;
use luya\helpers\ArrayHelper;
use luya\helpers\Html;

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
            'added_to_index' => Module::t('added_to_index'),
            'last_update' => Module::t('last_update'),
        ];
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

    public function ngRestScopes()
    {
        return [
            ['list',  ['title', 'url', 'last_update', 'added_to_index']],
            [['create', 'update'],  ['url', 'title', 'language_info', 'url_found_on_page', 'content', 'last_update', 'added_to_index']],
            ['delete', true],
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
        
        $index = self::generateRelevanceArray($query, $languageInfo);

        $ids = [];
        $order = [];
        foreach ($index as $row) {
            $ids[] = $row['id'];
            $order[] = new Expression("id={$row['id']} DESC");
        }

        $activeQuery = self::find()->where(['in', 'id', $ids]);
        if (!empty($ids)) {
            // sqlite wont work with FIELD()
            // alternative? https://stackoverflow.com/a/47368819/4611030
            $activeQuery->orderBy($order);
            // instead of:
            // $activeQuery->orderBy(new Expression('FIELD (id, ' . implode(', ', $ids) . ')'));
        }
        
        return $activeQuery;
    }
    
    /**
     * Generate relevance array for given querty and langauge info.
     *
     * @param [type] $query
     * @param [type] $languageInfo
     * @return void
     */
    public static function generateRelevanceArray($query, $languageInfo)
    {
        $parts = array_filter(explode(" ", $query));
        
        $index = [];
        foreach ($parts as $word) {
            $word = Stemm::stem($word, $languageInfo);
            $q = self::find()
                ->select(['id', 'url', 'title', 'content'])
                ->where([
                    'or',
                    ['like', 'content', $word],
                    ['like', 'description', $word],
                    ['like', 'title', $word],
                ]);
            if (!empty($languageInfo)) {
                $q->andWhere(['language_info' => $languageInfo]);
            }
            $data = $q->asArray()->indexBy('id')->all();
        
            // if there are no results one of the words does not exists, therefore return an empty array.
            // its better to tell people nothing is found instead of display a large amount of data for a
            // a single word (maybe the previous word had results)
            if (empty($data)) {
                return [];
            }

            static::indexer($word, $data, $index);
        }
        
        ArrayHelper::multisort($index, ['relevance', 'title'], [SORT_DESC, SORT_ASC]);

        return $index;
    }

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
                $item['relevance'] = static::calculatePageRelevanceValue($v, $keyword);
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
                    $newPos = static::calculatePageRelevanceValue($v, $keyword);

                    if ($newPos > $index[$id]['relevance']) {
                        $index[$id]['relevance'] = $newPos;
                    }
                }
            }
        }
    }

    /**
     * Get best word distance for a given words array.
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
     * Get the page importance value for a given item and keyword.
     * 
     * The bigger the value, the more relevante is this page for the given keyword.
     * 
     * @param array $item
     * @param [type] $keyword
     * @return void
     */
    private static function calculatePageRelevanceValue(array $item, $keyword)
    {
        $keyword = mb_strtolower($keyword);
        $url = mb_strtolower(parse_url($item['url'], PHP_URL_PATH));
        $posInUrl = self::getBestWordDistance(explode("/", $url), $keyword);
        $posInTitle = self::getBestWordDistance(explode(" ", mb_strtolower($item['title'])), $keyword);
        $partialWordCount = substr_count(mb_strtolower($item['content']), $keyword);
        $exactWordCount = preg_match_all('/\b'. preg_quote($keyword, '/') .'\b/', mb_strtolower($item['content']));


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
        return Html::encode($query);
    }

    /**
     * Returns the Searchdata ActiveQuery which is clsoes to the current query.
     *
     * @param string $query
     * @param string $languageInfo
     * @param integer $ignoreDistance
     * @return Searchdata
     */
    public static function didYouMean($query, $languageInfo, $ignoreDistance = 6)
    {
        $batch = Searchdata::find()
            ->select(['query', 'id'])
            ->where([
                'and',
                ['=', 'language', $languageInfo],
                ['>', 'results', 0]
            ])
            ->orderBy(['id' => SORT_ASC]) // makes sure always the first id is taken
            ->distinct()
            ->batch();

        $shortest = -1;
        
        $closest = false;
        foreach ($batch as $index) {
            foreach ($index as $word) {
                $lev = levenshtein($query, $word->query);

                if ($lev >= $ignoreDistance) {
                    continue;
                }

                if ($lev <= $shortest || $shortest < 0) {
                    $closest = $word;
                    $shortest = $lev;
                }
            }
        }

        return $closest;
    }
    

    /**
     * Generate preview from the search word and the corresponding cut amount.
     *
     * @param string $word The word too lookup in the `$content` variable.
     * @param number $cutAmount The amount of words on the left and right side of the word.
     * @return mixed
     */
    public function preview($word, $cutAmount = 150, $highlight = '<span style="background-color:#FFEBD1; color:black;">%s</span>')
    {
        $content = $this->content;
        // check if the word even exists in the content, as when stemming has taken place words may be cut.
        $exists = substr_count(mb_strtolower($content), $word);
        if ($exists == 0) {
            if (!empty($this->description)) {
                $content = $this->description;
            }
            $content = StringHelper::truncate($content, ($cutAmount*2), '..');

            return StringHelper::highlightWord($content, StringHelper::explode($word, " ", true, true), $highlight);
        }
        
        $cut = StringHelper::truncateMiddle($content, $word, $cutAmount);
        return StringHelper::highlightWord($cut, $word, $highlight);
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
}
