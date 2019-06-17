<?php

namespace luya\crawler\frontend\controllers;

use Yii;
use luya\crawler\models\Index;
use yii\helpers\Html;
use yii\data\ActiveDataProvider;
use luya\crawler\models\Searchdata;
use yii\data\ArrayDataProvider;

/**
 * Crawler Index Controller.
 *
 * Returns an {{\yii\data\ActiveDataProvider}} within $provider.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DefaultController extends \luya\web\Controller
{
    /**
     * Get search overview.
     *
     * The index action will return an active data provider object inside the $provider variable:
     *
     * ```php
     * foreach ($provider->models as $item) {
     *     var_dump($item);
     * }
     * ```
     *
     * @return string
     */
    public function actionIndex($query = null, $page = null, $group = null, $resolveId = null)
    {
        $language = Yii::$app->composition->getKey('langShortCode');
        $searchData = null;
        if (empty($query)) {
            $provider = new ArrayDataProvider([
                'allModels' => [],
            ]);
        } else {
            $activeQuery = Index::activeQuerySearch($query, $language);
            
            if (!empty($group)) {
                $activeQuery->andWhere(['group' => $group]);
            }
            
            $provider = new ActiveDataProvider([
                'query' => $activeQuery,
                'pagination' => [
                    'defaultPageSize' => $this->module->searchResultPageSize,
                    'route' => '/crawler/default',
                    'params' => ['query' => $query, 'page' => $page]
                ],
                'sort' => false,
            ]);

            $searchData = new Searchdata();
            $searchData->detachBehavior('LogBehavior');
            $searchData->attributes = [
                'query' => $query,
                'results' => $provider->totalCount,
                'timestamp' => time(),
                'language' => $language,
            ];
            $searchData->save();

            // if a resolve id is available
            if ($resolveId) {
                $emptyQuery = Searchdata::findOne($resolveId);
                if ($emptyQuery && empty($emptyQuery->resolved_by_didyoumean_searchdata_id)) {
                    $emptyQuery->resolved_by_didyoumean_searchdata_id = $searchData->id;
                    $emptyQuery->update();
                }
            }
        }
        
        return $this->render('index', [
            'query' => Html::encode($query),
            'provider' => $provider,
            'language' => $language,
            'searchModel' => $searchData,
        ]);
    }
}
