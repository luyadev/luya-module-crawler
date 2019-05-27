<?php

namespace luya\crawler\tests;


use luya\testsuite\fixtures\NgRestModelFixture;
use luya\crawler\models\Searchdata;
use luya\crawler\tests\data\fixtures\IndexFixture;
use luya\crawler\models\Builderindex;

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
    }

    public function beforeTearDown()
    {
        parent::beforeTearDown();

        $this->searchDataFixture->cleanup();
        $this->builderindexFixture->cleanup();
    }
}