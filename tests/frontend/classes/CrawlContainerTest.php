<?php

namespace luya\crawler\tests\frontend\classes;

use luya\crawler\tests\CrawlerTestCase;
use luya\crawler\frontend\classes\CrawlContainer;

class CrawlContainerTest extends CrawlerTestCase
{
    public function testEmptyBaseUrl()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $container = new CrawlContainer();
        $container->start();
    }
    
    public function testInvalidBaseUrlCallLog()
    {
        $container = new CrawlContainer(['baseUrl' => 'http://localhost/unknown/url']);
        $d = $container->getReport();
        $this->assertSame(1, count($d));
    }
}