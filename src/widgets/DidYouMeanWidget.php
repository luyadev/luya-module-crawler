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

    public $route = '/crawler/default';

    public $tagOptions = [];

    public $linkOptions = [];

    public function init()
    {
        parent::init();

        if (!$this->query || !$this->language || !$this->_dataProvider) {
            throw new InvalidConfigException("The query, language and dataProvider properties can not be null.");
        }
    }

    private $_dataProvider;

    public function setDataProvider(ActiveDataProvider $provider)
    {
        $this->_dataProvider = $provider;
    }

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