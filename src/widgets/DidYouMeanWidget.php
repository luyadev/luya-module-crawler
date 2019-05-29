<?php

namespace luya\crawler\widgets;

use luya\base\Widget;
use luya\crawler\models\Index;
use luya\helpers\Html;
use yii\base\InvalidConfigException;
use luya\crawler\frontend\Module;
use yii\data\DataProviderInterface;
use luya\crawler\models\Searchdata;

/**
 * Did you mean?
 *
 * Returns a did you mean klickable link based on search input data.
 *
 * Use the search model to link data between search and did you mean suggestion:
 *
 * ```php
 * DidYouMeanWidget::widget([
 *     'searchModel' => $searchModel,
 *     'dataProvider' => $provider,
 * ]);
 * ```
 *
 * Or without search model
 *
 * ```php
 * DidYouMeanWidget::widget([
 *     'query' => $query,
 *     'language' => $language,
 *     'dataProvider' => $provider,
 * ]);
 * ```
 *
 * @since 1.0.5
 */
class DidYouMeanWidget extends Widget
{
    /**
     * @var string The query from the search request.
     */
    public $query;

    /**
     * @var string The language determines on what index the did you mean suggestion should be made.
     */
    public $language;

    /**
     * @var mixed The route which is used for the href tag.
     */
    public $route = '/crawler/default';

    /**
     * @var array Optional arguments for the wrapper pragraph (p).
     */
    public $tagOptions = [];

    /**
     * @var array Optional arguments for the link (a) html tag.
     */
    public $linkOptions = [];

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->language || $this->resultsCount === null) {
            throw new InvalidConfigException("The language and resultsCount properties can not be null. Either provider those property or set searchModel.");
        }
    }

    private $_searchModel;

    /**
     * User search model to store informations.
     *
     * @param Searchdata $search
     * @since 2.0.0
     */
    public function setSearchModel(Searchdata $search)
    {
        $this->_searchModel = $search;
        $this->language = $search->language;
        $this->query = $search->query;
        $this->resultsCount = $search->results;
    }

    private $_dataProvider;

    /**
     * Setter method for data provider
     *
     * @param DataProviderInterface $provider
     */
    public function setDataProvider(DataProviderInterface $provider)
    {
        $this->_dataProvider = $provider;
        $this->resultsCount = $provider->getTotalCount();
    }

    protected $resultsCount = null;

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        if (empty($this->query) || $this->resultsCount > 0) {
            return;
        }

        $didYouMean = Index::didYouMean($this->query, $this->language);

        if ($didYouMean) {
            $didYouMean->updateCounters(['didyoumean_suggestion_count' => 1]);
            $params = [$this->route, 'query' => $didYouMean->query];
            if ($this->_searchModel) {
                $params['resolveId'] = $this->_searchModel->id;
            }
            $content = Html::a(Module::t("Did you mean <b>{word}</b>?", ['word' => $didYouMean->query]), $params, $this->linkOptions);
            return Html::tag('p', $content, $this->tagOptions);
        }
    }
}
