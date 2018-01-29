<?php

namespace crawlerests;

use luya\testsuite\cases\WebApplicationTestCase;

/**
 * Crawler TestCase
 * @author Basil Suter <basil@nadar.io>
 */
class CrawlerTestCase extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
           'id' => 'mytestapp',
           'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => DB_DSN,
                    'username' => DB_USER,
                    'password' => DB_PASS,
                    'charset' => 'utf8',
                ],
                'request' => [
                    'forceWebRequest' => true,
                ],
            ],
            'modules' => [
                'crawleradmin' => 'luya\crawler\admin\Module',
            ]
        ];
    }
}
