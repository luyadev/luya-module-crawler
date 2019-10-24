<?php

namespace luya\crawler\tests\frontend\classes;

use luya\crawler\tests\CrawlerTestCase;
use luya\crawler\frontend\classes\CrawlContainer;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use luya\crawler\models\Link;
use luya\testsuite\fixtures\NgRestModelFixture;

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

    /**
     * @runInSeparateProcess
     */
    public function testStartWithFinish()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Builderindex::class,
            'fixtureData' => [
                1 => [
                    'url' => 'url',
                    'content' => 'content',
                    'title' => 'title',
                    'last_indexed' => 'last_indexed',
                    'language_info' => 'en',
                    'crawled' => 1,
                    'is_dublication' => false,
                ],
                2 => [
                    'url' => 'empty',
                    'content' => '',
                    'title' => '',
                    'last_indexed' => 'last_indexed',
                    'language_info' => 'en',
                    'crawled' => 1,
                    'is_dublication' => false,
                ],
                
            ]
        ]);

        $content = new NgRestModelFixture([
            'modelClass' => Index::class,
            'fixtureData' => [
                1 => [
                    'content' => null,
                    'title' => '',
                ],
                2 => [
                    'url' => 'empty',
                    'content' => null,
                    'title' => '',
                ],
            ]
        ]);

        $link = new NgRestModelFixture([
            'modelClass' => Link::class,
            'fixtureData' => [
                1 => [
                    'url' => 'url.html',
                    'url_found_on_page' => 'ref.html',
                    'title' => 'Title of Page',
                    'response_status' => 200,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'is_ignored' => 0,
                ]
            ]
        ]);
        

        $container = new CrawlContainer(['baseUrl' => 'https://notfound']);
        $container->verbose = 1;
        $r = $container->start();
        $this->assertNull($r);

        $log = $container->getReport();

        $this->assertSame(14, count($log));
    }
}
