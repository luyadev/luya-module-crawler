<?php
use luya\helpers\Url;
use yii\widgets\LinkPager;
use luya\crawler\widgets\DidYouMeanWidget;
/* @var $query string The lookup query encoded */
/* @var $language string */
/* @var $this \luya\web\View */
/* @var $provider \yii\data\ActiveDataProvider */
/* @var $searchModel \luya\crawler\models\Searchdata */
?>

<form class="searchpage__searched-form" action="<?= Url::toRoute(['/crawler/default/index']); ?>" method="get">
    <input id="search" name="query" type="search" value="<?= $query ?>">
    <input type="submit" value="Search"/>
</form>

<h2><?= $provider->totalCount; ?> Results</h2>

<?php if ($query && $provider->totalCount == 0): ?>
    <div>No results found for &laquo;<?= $query; ?>&raquo;.</div>
<?php endif; ?>

<?= DidYouMeanWidget::widget(['searchModel' => $searchModel]); ?>
<?php foreach($provider->models as $item): /* @var $item \luya\crawler\models\Index */ ?>
    <h3><?= $item->title; ?></h3>
    <p><?= $item->preview($query); ?></p>
    <a href="<?= $item->url; ?>"><?= $item->url; ?></a>
<?php endforeach; ?>
<?= LinkPager::widget(['pagination' => $provider->pagination]); ?>
