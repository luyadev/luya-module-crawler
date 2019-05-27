<?php

namespace luya\crawler\tests\widgets;

use luya\crawler\tests\CrawlerTestCase;
use luya\crawler\widgets\DidYouMeanWidget;
use yii\data\ArrayDataProvider;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\crawler\models\Searchdata;


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
}