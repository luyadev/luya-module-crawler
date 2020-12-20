<?php

namespace luya\crawler\tests\widgets;

use luya\crawler\tests\CrawlerTestCase;
use luya\crawler\widgets\DidYouMeanWidget;
use yii\data\ArrayDataProvider;
use luya\crawler\models\Index;

class DidYouMeanWidgetTest extends CrawlerTestCase
{
    public function testLength()
    {
        $string256 = 'XVH7jCOxgZg229KRxNKxtI7xIOjOKBjQH9Vlu8e3GvAThVFf4TMapqlHHV5Qn32uikOMblKdIcru2mYWHPSyCShdDarRpkR20Bo2ubE2uUI7f3IFt2JPjCHzt5PzkUBuyWTdlmoE148zS3VYbfqi2dE7otM4oBebaXDksFJYyIi0hghr1AQYkAQYpfiKbhRhBaycluL9vH8LyegbVq38Bpnkgry9sJxh9loxQcm7rgw7eoZeHNbR0LFf0O16xI8t';

        $this->assertFalse(Index::didYouMean($string256, 'de'));

        $string255 = 'VH7jCOxgZg229KRxNKxtI7xIOjOKBjQH9Vlu8e3GvAThVFf4TMapqlHHV5Qn32uikOMblKdIcru2mYWHPSyCShdDarRpkR20Bo2ubE2uUI7f3IFt2JPjCHzt5PzkUBuyWTdlmoE148zS3VYbfqi2dE7otM4oBebaXDksFJYyIi0hghr1AQYkAQYpfiKbhRhBaycluL9vH8LyegbVq38Bpnkgry9sJxh9loxQcm7rgw7eoZeHNbR0LFf0O16xI8t';

        $this->assertFalse(Index::didYouMean($string255, 'de'));
    }

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

        $this->assertStringContainsString('Did you mean <b>john doe</b>', $widget);
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

        $this->assertStringContainsString('resolveId=3', $widget);
    }

    public function testDidYouMeanWithSearchData()
    {
        // distance between index and john doe is to big
        $this->assertFalse(Index::didYouMean('index', 'en'));
        $r = Index::didYouMean('john doa', 'en');
    }

    public function testEmptyDidYouMean()
    {
        $this->assertSame('', DidYouMeanWidget::widget());
        $this->assertSame('', DidYouMeanWidget::widget(['searchModel' => null]));
    }
}
