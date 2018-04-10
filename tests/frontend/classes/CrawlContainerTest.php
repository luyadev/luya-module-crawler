<?php

namespace crawlerests\frontend\classes;

use crawlerests\CrawlerTestCase;
use luya\crawler\frontend\classes\CrawlContainer;

class CrawlContainerTest extends CrawlerTestCase
{
    public function testEmptyBaseUrl()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $container = new CrawlContainer();
    }
    
    public function testInvalidBaseUrlCall()
    {
        $container = new CrawlContainer(['baseUrl' => 'http://localhost/unknown/url']);
        $d = $container->getReport();
        
        $this->assertSame(1, count($d));
    }
}