<?php

namespace luya\crawler\tests\widgets;

use luya\crawler\tests\CrawlerTestCase;
use luya\crawler\widgets\DidYouMeanWidget;
use yii\data\ArrayDataProvider;
use luya\crawler\models\Index;

class DidYouMeanWidgetTest extends CrawlerTestCase
{
    public function testRunNoIndex()
    {
        $provider = new ArrayDataProvider([
            'allModels' => [
                ['bar' => 'foo']
            ],
        ]);

        $widget = DidYouMeanWidget::widget([
            'dataProvider' => $provider,
            'query' => 'jon doe',
            'language' => 'en',
        ]);

        $this->assertSame('', $widget);
    }

    public function testRunSuggestion()
    {
        $provider = new ArrayDataProvider([
            'allModels' => [],
        ]);

        $widget = DidYouMeanWidget::widget([
            'dataProvider' => $provider,
            'query' => 'jon doe',
            'language' => 'en',
        ]);

        $this->assertContains('Did you mean <b>john doe</b>', $widget);
    }

    public function testRunWithSearchModel()
    {
        $provider = new ArrayDataProvider([
            'allModels' => [],
        ]);

        $model = $this->searchDataFixture->newModel;
        $model->query = 'jane';
        $model->language = 'en';
        $model->timestamp = time();
        $model->results = 0;
        $modelStatus = $model->save();

        $this->assertNotFalse($modelStatus);

        $widget = DidYouMeanWidget::widget([
            'dataProvider' => $provider,
            'searchModel' => $model,
        ]);

        $this->assertContains('resolveId=3', $widget);
    }

    public function testDidYouMeanWithSearchData()
    {
        // distance between index and john doe is to big
        $this->assertFalse(Index::didYouMean('index', 'en'));
        $r = Index::didYouMean('john doa', 'en');
    }
}
