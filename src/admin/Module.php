<?php

namespace luya\crawler\admin;

use luya\admin\components\AdminMenuBuilder;

/**
 * LUYA Crawler Admin Module.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class Module extends \luya\admin\base\Module
{
    /**
     * @inheritdoc
     */
    public $apis = [
        'api-crawler-builderindex' => 'luya\crawler\admin\apis\BuilderindexController',
        'api-crawler-index' => 'luya\crawler\admin\apis\IndexController',
        'api-crawler-searchdata' => 'luya\crawler\admin\apis\SearchdataController',
    ];
    
    public $dashboardObjects = [
        [
            'class' => 'luya\admin\dashboard\ListDashboardObject',
            'template' => '<li class="list-group-item" ng-repeat="item in data">{{item.query}} <small>({{item.language}})</small><span class="badge badge-info float-right">{{item.timestamp * 1000 | date:\'short\'}}</span></li>',
            'dataApiUrl' => 'admin/api-crawler-searchdata/latest',
            'title' => ['crawleradmin', 'dashboard_title'],
        ],
    ];

    /**
     * @inheritdoc
     */
    public function getMenu()
    {
        return (new AdminMenuBuilder($this))->node('crawler', 'find_in_page')
        ->group('crawler_index')
            ->itemApi('crawler_index', 'crawleradmin/index/index', 'list', 'api-crawler-index')
        ->group('crawler_analytics')
            ->itemApi('crawler_analytics_queries', 'crawleradmin/searchdata/index', 'label', 'api-crawler-searchdata');
    }
    
    /**
     * @inheritdoc
     */
    public static function onLoad()
    {
        self::registerTranslation('crawleradmin', static::staticBasePath() . '/messages', [
            'crawleradmin' => 'crawleradmin.php',
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function t($message, array $params = [])
    {
        return parent::baseT('crawleradmin', $message, $params);
    }
}
