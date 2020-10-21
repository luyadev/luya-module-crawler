<?php

namespace luya\crawler\tests;

use luya\testsuite\fixtures\NgRestModelFixture;
use luya\crawler\models\Searchdata;
use luya\crawler\tests\data\fixtures\IndexFixture;
use luya\crawler\models\Builderindex;
use luya\testsuite\fixtures\ActiveRecordFixture;

trait TableSetupTrait
{
    public $searchDataFixture;

    public $indexFixture;

    public $builderindexFixture;

    public function afterSetup()
    {
        parent::afterSetup();

        $this->searchDataFixture = new NgRestModelFixture([
            'modelClass' => Searchdata::class,
            'fixtureData' => [
                'model1' => [
                    'id' => 2,
                    'query' => 'john doe',
                    'results' => 1,
                    'language' => 'en',
                    'timestamp' => time(),
                ]
            ]
        ]);

        $this->indexFixture = new IndexFixture();

        $this->builderindexFixture = new NgRestModelFixture([
            'modelClass' => Builderindex::class,
        ]);

        new ActiveRecordFixture([
            'tableName' => 'crawler_builder_runtime_url',
            'schema' => [
                'url' => 'text',
            ]
        ]);

        new ActiveRecordFixture([
            'tableName' => 'crawler_builder_runtime_checksum',
            'schema' => [
                'checksum' => 'text',
            ]
        ]);

        new ActiveRecordFixture([
            'tableName' => 'crawler_builder_runtime_queue',
            'schema' => [
                'url' => 'text',
                'referrer_url' => 'text',
            ]
        ]);
    }

    public function beforeTearDown()
    {
        parent::beforeTearDown();

        $this->searchDataFixture->cleanup();
        $this->builderindexFixture->cleanup();
    }
}
