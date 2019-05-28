<?php
use luya\admin\helpers\Angular;
use luya\crawler\admin\Module;
?>
<script>
zaa.bootstrap.register('InlineController', ['$scope', '$http', function($scope, $http) {

    $scope.data = null;
    $scope.from = null;
    $scope.to = null;
    $scope.noResults = [];
    $scope.suggestions = [];

    $scope.loadData = function(from, to) {
        $http.post('crawleradmin/stats/data', {from:from, to:to}).then(function(r) {
            $scope.data = r.data.echarts;
            $scope.noResults = r.data.noResults;
            $scope.suggestions = r.data.suggestions;
        });
    };

    $scope.$watch('from', function(n, o) {
        if (n!=o) {
            $scope.loadData(n, $scope.to);
        }
    });

    $scope.$watch('to', function(n, o) {
        if (n!=o) {
            $scope.loadData($scope.from, n);
        }
    });

    $scope.loadData();
}]);
</script>
<div ng-controller="InlineController">
    <div class="form-inline justify-content-md-center">
        <?= Angular::date('from', Module::t('stats_from')); ?>
        <?= Angular::date('to', Module::t('stats_to')); ?>
    </div>
    <div class="row mt-4">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <?= Module::t('stats_searches_title') ;?>
                </div>
                <div class="card-body">
                    <echarts id="chart" legend="legend" data="data"></echarts>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <?= Module::t('stats_no_results_title') ;?>
                </div>
                <div class="card-body p-2">
                    <table class="table table-striped mb-0">
                        <tr ng-repeat="item in noResults">
                            <td>{{item.query}}</td>
                            <td>{{item.count}}</td>
                        </tr>
                    </table>
                    <p class="text text-muted mb-0 p-2" ng-show="noResults.length == 0"><?= Module::t('stats_no_data'); ?></p>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <?= Module::t('stats_most_suggestions_title'); ?>
                </div>
                <div class="card-body p-2">
                    <table class="table table-striped mb-0">
                        <tr ng-repeat="item in suggestions">
                            <td>{{item.query}}</td>
                            <td>{{item.count}}</td>
                        </tr>
                    </table>
                    <p class="text text-muted mb-0 p-2" ng-show="suggestions.length == 0"><?= Module::t('stats_no_data'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>