<?php

namespace luya\crawler\tests;

use luya\testsuite\cases\ConsoleApplicationTestCase;

class ConsoleCrawlerTestCase extends ConsoleApplicationTestCase
{
    use TableSetupTrait;

    public function getConfigArray()
    {
        return [
           'id' => 'mytestappcli',
           'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                    'charset' => 'utf8',
                ],
            ],
            'modules' => [
                'crawleradmin' => 'luya\crawler\admin\Module',
            ]
        ];
    }
}
