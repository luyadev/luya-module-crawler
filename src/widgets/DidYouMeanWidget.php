<?php

namespace luya\crawler\widgets;

use luya\base\Widget;
use yii\data\ActiveDataProvider;
use luya\crawler\models\Index;
use luya\helpers\Html;
use yii\base\InvalidConfigException;
use luya\crawler\frontend\Module;

/**
 * Returns a did you mean klickable link based on search input data.
 * 
 * ```php
 * DidYouMeanWidget::widget([
 *  'query' => $query,
 *  'language' => $language,
 *  'dataProvider' => $provider,
 * ]);
 * ```
 * 
 * @since 1.0.5
 */
class DidYouMeanWidget extends Widget
{
    public $query;

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

        if (!$this->query || !$this->language || !$this->_dataProvider) {
            throw new InvalidConfigException("The query, language and dataProvider properties can not be null.");
        }
    }

    private $_dataProvider;

    /**
     * Setter method for data provider
     *
     * @param ActiveDataProvider $provider
     */
    public function setDataProvider(ActiveDataProvider $provider)
    {
        $this->_dataProvider = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        if ($this->_dataProvider->totalCount > 0) {
            return;
        }

        $didYouMean = Index::didYouMean($this->query, $this->language);

        if ($didYouMean) {
            $content = Html::a(Module::t("did_you_mean", ['word' => $didYouMean]), [$this->route, 'query' => $didYouMean], $this->linkOptions);
            return Html::tag('p', $content, $this->tagOptions);
        }
    }
}