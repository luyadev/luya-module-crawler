<?php

namespace luya\crawler\tests\frontend\commands;

use luya\crawler\tests\ConsoleCrawlerTestCase;
use luya\crawler\frontend\Module;
use luya\crawler\CrawlIndexInterface;
use luya\crawler\frontend\commands\CrawlController;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use luya\crawler\models\Link;
use luya\testsuite\fixtures\NgRestModelFixture;

class CrawlControllerTest extends ConsoleCrawlerTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testIndexerInterface()
    {
        new NgRestModelFixture(['modelClass' => Link::class]);
        $module = new Module('frontendcrawler');
        $module->baseUrl = 'https://luya.io';
        $module->indexer = [
            MyTestIndexer::class,
        ];

        $folder = dirname(__FILE__) . '/../../data/runtime';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ctrl = new CrawlController('crawler', $module);
        $ctrl->verbose = 0;
        $ctrl->interactive = 0;
        $ctrl->runtimeFolder = $folder;
        $ctrl->runAction('index');

        $this->assertSame('6', Builderindex::find()->asArray()->count());
        $this->assertSame('6', Index::find()->asArray()->count());
    }
}


class MyTestIndexer implements CrawlIndexInterface
{
    public static function indexLinks()
    {
        return [
            'http://localhost/path' => 'LUYA Website',
        ];
    }
}
