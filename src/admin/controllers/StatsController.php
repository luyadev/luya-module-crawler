<?php

namespace luya\crawler\admin\controllers;

use Yii;
use DateInterval;
use DatePeriod;
use DateTime;
use luya\admin\base\Controller;
use luya\helpers\Json;
use luya\crawler\models\Searchdata;
use luya\crawler\admin\Module;

class StatsController extends Controller
{
    public $disablePermissionCheck = true;

    public $apiResponseActions = ['data'];

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionData()
    {
        $start = Yii::$app->request->getBodyParam('from', strtotime("-1 week 00:00"));
        $end = Yii::$app->request->getBodyParam('to', time());

        list ($daysRange, $series) = $this->generateSeriesData($start, $end);

        $noResults = Searchdata::find()
            ->where(['between', 'timestamp', $start, $end])
            ->select(['query', 'count' => 'count(query)'])
            ->groupBy(['query'])
            ->andWhere(['results' => 0])
            ->orderBy(['count' => SORT_DESC])
            ->asArray()
            ->all();

        $suggestions = Searchdata::find()
            ->where(['between', 'timestamp', $start, $end])
            ->select(['query', 'count' => 'sum(didyoumean_suggestion_count)'])
            ->groupBy(['query'])
            ->andWhere(['>', 'didyoumean_suggestion_count', 0])
            ->orderBy(['count' => SORT_DESC])
            ->asArray()
            ->all();

        return [
            'echarts' => $this->generateEchartsJson($daysRange, $series),
            'noResults' => $noResults,
            'suggestions' => $suggestions,
        ];
    }

    private function generateSeriesData($start, $end)
    {
        
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod((new Datetime())->setTimestamp($start), $interval, (new Datetime())->setTimestamp($end));
        $daysRange = [];
        foreach ($daterange as $date) {
            $daysRange[$this->timestampToDay($date->getTimestamp())] = 0;
        }
        $queries = Searchdata::find()
            ->where(['between', 'timestamp', $start, $end])
            ->select(['results', 'timestamp'])
            ->asArray()
            ->all();

        $series = [
            'total' => $daysRange,
            'noResults' => $daysRange,
            'results' => $daysRange,
        ];

        foreach ($queries as $item) {
            $day = $this->timestampToDay($item['timestamp']);
            $series['total'][$day] = isset($series['total'][$day]) ? $series['total'][$day] + 1 : 1;
            if ($item['results'] > 0) {
                $series['results'][$day] = isset($series['results'][$day]) ? $series['results'][$day] + 1 : 1;
            } else {
                $series['noResults'][$day] = isset($series['noResults'][$day]) ? $series['noResults'][$day] + 1 : 1;
            }
        }

        return [$daysRange, $series];
    }

    private function timestampToDay($timestamp)
    {
        return strtotime("midnight", $timestamp);
    }

    private function generateEchartsJson($daysRange, $series)
    {
        $days = [];
        foreach (array_keys($daysRange) as $item) {
            $days[] = strftime("%a, %e.%b", $item);
        }
        return [
            'tooltip' => [
                'trigger' => 'axis',
            ],
            'legend' => [
                'data' => [Module::t('legend_total'), Module::t('legend_no_results'), Module::t('legend_results')],
            ],
            'xAxis' => ['type' => 'category', 'boundaryGap' => false, 'data' => $days],
            'yAxis' => ['type' => 'value'],
            'series' => [
                [
                    'name' => Module::t('legend_total'),
                    'data' => array_values($series['total']),
                    'type' => 'line',
                ],
                [
                    'name' => Module::t('legend_no_results'),
                    'data' => array_values($series['noResults']),
                    'type' => 'line',
                ],
                [
                    'name' => Module::t('legend_results'),
                    'data' => array_values($series['results']),
                    'type' => 'line',
                ],
            ]
        ];
    }
}