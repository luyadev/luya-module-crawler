<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# Crawler

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-module-crawler/v/stable)](https://packagist.org/packages/luyadev/luya-module-crawler)
[![Test Coverage](https://api.codeclimate.com/v1/badges/fbf752bd8ed584de427b/test_coverage)](https://codeclimate.com/github/luyadev/luya-module-crawler/test_coverage)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-crawler/downloads)](https://packagist.org/packages/luyadev/luya-module-crawler)
![Tests](https://github.com/luyadev/luya-module-crawler/workflows/Tests/badge.svg)

An easy to use full-website page crawler to make provide search results on your page. The crawler module gather all information about the sites on the configured domain and stores the index in the database. From there you can now create search queries to provide search results. There are also helper methods which provide intelligent search results by splitting the input into multiple search queries (used by default).

![LUYA Crawler Search Stats](https://raw.githubusercontent.com/luyadev/luya-module-crawler/master/crawler-stats.png)

## Installation

Install the module via composer:

```sh
composer require luyadev/luya-module-crawler:^3.0
```

After installation via Composer include the module to your configuration file within the modules section.

```php
'modules' => [
    //...
    'crawler' => [
        'class' => 'luya\crawler\frontend\Module',
        'baseUrl' => 'https://luya.io',
    ],
    'crawleradmin' => 'luya\crawler\admin\Module',
]
```

> Where `baseUrl` is the domain you want to crawler all information.

After setup the module in your config you have to run the migrations and import command (to setup permissions):

```sh
./vendor/bin/luya migrate
./vendor/bin/luya import
```

## Running the Crawler

To execute the command (and run the crawler proccess) use the crawler command `crawl`, you should put this command in cronjob to make sure your index is up-to-date:

> Make sure your page is in utf8 mode (`<meta charset="utf-8"/>`) and make sure to set the language `<html lang="<?= Yii::$app->composition->langShortCode; ?>">`.

```sh
./vendor/bin/luya crawler/crawl
```

> In order to provide current crawl results you should create a cronjob which crawls the page each night: `cd httpdocs/current && ./vendor/bin/luya crawler/crawl`

### Crawler Arguments

All crawler arguments for `crawler/crawl`, an example would be `crawler/crawl --pdfs=0 --concurrent=5 --linkcheck=0`:

|name|description|default
|----|-----------|-------
|linkcheck|Whether all links should be checked after the crawler has indexed your site|true
|pdfs|Whether PDFs should be indexed by the crawler or not|true
|concurrent|The amount of conccurent page crawles|15

## Stats

You can also get statistic results enabling a cronjob executing each week:
 
```
./vendor/bin/luya crawler/statistic
```


## Create search form

Make a post request with `query` to the `crawler/default/index` route and render the view as follows:

```php
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
```

### Crawler Settings

You can use crawler tags to trigger certains events or store informations:

|tag|example|description
|---|-------|-----------
|CRAWL_IGNORE|`<!-- [CRAWL_IGNORE] -->Ignore this<!-- [/CRAWL_IGNORE] -->`|Ignores a certain content from indexing.
|CRAWL_FULL_IGNORE|`<!-- [CRAWL_FULL_IGNORE] --> `|Ignore a full page for the crawler, keep in mind that links will be added to index inside the ignore page.
|CRAWL_GROUP|`<!-- [CRAWL_GROUP]api[/CRAWL_GROUP] -->`|Sometimes you want to group your results by a section of a page, in order to let crawler know about the group/section of your current page. Now you can group your results by the `group` field.
|CRAWL_TITLE|`<!-- [CRAWL_TITLE]My Title[/CRAWL_TITLE] -->`|If you want to make sure to always use your customized title you can use the CRAWL_TITLE tag to ensure your title for the page:
