<?php

namespace luya\crawler\frontend\classes;

use Yii;
use luya\Exception;
use yii\base\InvalidConfigException;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Index;
use luya\helpers\Url;
use yii\base\BaseObject;
use luya\helpers\Html;
use GuzzleHttp\Client;
use luya\crawler\models\Link;

/**
 * Crawler Container.
 *
 * The Crawler Container contains the whole process of the build. Returns a log and contains the informations about the pages
 * which should be crawle.d
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CrawlContainer extends BaseObject
{
    /**
     * @var string The based Url where the crawler should start to lookup for pages, the crawler only allowes
     * links which matches the base url. It doenst matter if you have a trailing slash or not, the module is taking
     * care of this.
     *
     * So on a localhost your base url could look like this:
     *
     * ```php
     * 'baseUrl' => 'http://localhost/luya-kickstarter/public_html/',
     * ```
     *
     * If you are on a production/preproduction server the url in your config could look like this:
     *
     * ```php
     * 'baseUrl' => 'https://luya.io',
     * ```
     */
    public $baseUrl;

    /**
     * @var string The base host extracted from baseUrl
     */
    public $baseHost;

    /**
     * @var array An array with regular expression (including delimiters) which will be applied to found links so you can
     * filter several urls which should not be followed by the crawler.
     *
     * Examples:
     *
     * ```php
     * 'filterRegex' => [
     *     '/\.\//i',           // filter all links with a dot inside
     *     '/agenda\//i',       // filter all pages who contains "agenda/"
     * ],
     * ```
     */
    public $filterRegex = [];
    
    /**
     * @var boolean Whether verbositiy is enabled or not.
     */
    public $verbose = false;

    /**
     * @var array Define an array of extension where the links should automatically not follow in order to save memory.
     */
    public $doNotFollowExtensions = [];
    
    /**
     * @var boolean By default the title tag will be used for the page name, if `$useH1` is enabled the title for the page will be replaced by the h1 tag if found, oterwise
     * only the title tag is used for titles.
     */
    public $useH1 = false;

    /**
     * @var integer The time when the crawler starts
     */
    public $startTime;
    
    private $_proccessed = [];
    
    protected function addProcessed($link)
    {
        $this->_proccessed[] = $link;
    }
    
    protected function isProcessed($link)
    {
        return in_array($link, $this->_proccessed);
    }
    
    private $_log = [];

    public function addLog($cat, $url, $message = null)
    {
        /**
         * public $log = [
         *    'new' => [],
         *    'update' => [],
         *    'delete' => [],
         *    'delete_issue' => [],
         *    'unchanged' => [],
         *    'filtered' => [],
         * ];
         */
        $this->_log[] = [$cat, $url, $message];
    }
    
    public function verbosePrint($key, $value = null)
    {
        if ($this->verbose) {
            $value = is_array($value) ? print_r($value, true) : $value;
            
            echo '+ ' . $key . ' =========> ' . $value . PHP_EOL;
        }
    }

    private $_crawlers = [];

    /**
     * Get the crawl page object based on its ulr.
     *
     * @param string $url The crawler object.
     * @return \luya\crawler\frontend\classes\CrawlPage
     */
    protected function getCrawler($url)
    {
        if (!array_key_exists($url, $this->_crawlers)) {
            $crawler = new CrawlPage(['baseUrl' => $this->baseUrl, 'pageUrl' => $url, 'verbose' => $this->verbose, 'useH1' => $this->useH1]);
            $this->_crawlers[$url] = $crawler;
        }

        return $this->_crawlers[$url];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->baseUrl === null) {
            throw new InvalidConfigException("argument 'baseUrl' can not be null.");
        }
        
        $this->startTime = time();
        $this->baseUrl = Url::trailing($this->baseUrl);
        $this->baseHost = parse_url($this->baseUrl, PHP_URL_HOST);
        
        if (!$this->isBaseUrlExists()) {
            return $this->addLog(null, "The given baseUrl '{$this->baseUrl}' is wrong or offline. Status code must be 200.");
        }
        
        $this->verbosePrint('baseUrl', $this->baseUrl);
        $this->verbosePrint('baseHost', $this->baseHost);
        $this->verbosePrint('useH1', $this->useH1);
        $this->verbosePrint('filterRegex', $this->filterRegex);
        $this->verbosePrint('doNotFollowExtensions', $this->doNotFollowExtensions);
        
        Yii::$app->db->createCommand()->truncateTable(Builderindex::tableName())->execute();

        $this->verbosePrint('truncate of table crawerl_builder_index', 'yes');
    }

    /**
     * Start the crawler process.
     *
     * @since 2.0.0
     */
    public function start()
    {
        $this->urlStatus($this->baseUrl);
        $this->find();
    }
    
    /**
     * Checks whether the base url response a status 200 code.
     *
     * @return boolean
     * @since 1.0.2
     */
    public function isBaseUrlExists()
    {
        try {
            $client = new Client();
            // create request but disabled guzzle exception by passing http_errors false
            $result = $client->request('GET', $this->baseUrl, ['http_errors' => false]);
            // see if status code is 200
            return $result->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function find()
    {
        foreach (Builderindex::find()->where(['crawled' => false])->select(['url'])->asArray()->all() as $item) {
            if (!$this->isProcessed($item['url'])) {
                if ($this->urlStatus($item['url'])) {
                    $this->addProcessed($item['url']);
                }
            } else {
                $this->verbosePrint('url is in processed array and will therfore skipped.', $item['url']);
            }
        }

        if (Builderindex::find()->where(['crawled' => false])->exists()) {
            $this->verbosePrint("run another find process");
            $this->find();
        } else {
            $this->verbosePrint("All pages has been crawled, proceed with finish() function.");
            $this->finish();
        }
    }

    public function getReport()
    {
        return $this->_log;
    }

    public function finish()
    {
        $builder = Builderindex::find()->where(['is_dublication' => false])->indexBy('url')->asArray()->all();
        $index = Index::find()->asArray()->indexBy('url')->all();

        if (count($builder) == 0) {
            throw new Exception('The crawler have not found any results. Wrong base url? Or set a rule which tracks all urls? Try to enable verbose output.');
        }

        $this->verbosePrint("syncronize the builder index into the real page index.");
        foreach ($builder as $url => $page) {
            if (isset($index[$url])) { // page exists in index
                if ($index[$url]['content'] == $page['content']) {
                    $this->addLog('unchanged', $url, $page['title']);
                    $update = Index::findOne(['url' => $url]);
                    $update->updateAttributes(['title' => $page['title']]);
                } else {
                    $this->addLog('update', $url, $page['title']);
                    $update = Index::findOne(['url' => $url]);
                    $update->attributes = $page;
                    $update->last_update = time();
                    $update->save(false);
                }
                unset($index[$url]);
            } else {
                $this->addLog('new', $url, $page['title']);
                $insert = new Index();
                $insert->attributes = $page;
                $insert->added_to_index = time();
                $insert->last_update = time();
                $insert->save(false);
            }
        }

        $this->verbosePrint("Delete pages from the index which are not existing anymore.");
        // delete not unseted urls from index
        foreach ($index as $deleteUrl => $deletePage) {
            $this->addLog('delete', $deleteUrl, $deletePage['title']);
            $model = Index::findOne($deletePage['id']);
            $model->delete(false);
        }

        // delete empty content empty title
        $this->verbosePrint("Delete pages with empty content.");
        foreach (Index::find()->where(['=', 'content', ''])->orWhere(['=', 'title', ''])->all() as $page) {
            $this->addLog('delete_issue', $page->url, $page->title);
            $page->delete(false);
        }

        $this->verbosePrint("Start cleanup the Link index");
        Link::cleanup($this->startTime);
        $this->verbosePrint("Update the link status");
        Link::updateLinkStatus();
    }

    public function matchBaseUrl($url)
    {
        if (strpos($url, $this->baseUrl) === false) {
            $this->verbosePrint("url '$url' does not match baseUrl '{$this->baseUrl}'");
            return false;
        }
        
        return true;
    }
    
    /**
     *
     * @param unknown $file
     * @return boolean true = valid; false = invalid does not match
     */
    public function filterExtensionFile($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (in_array(strtolower($extension), $this->doNotFollowExtensions)) {
            $this->verbosePrint('extenion is in doNotFollowExtensions list', $extension);
            return false;
        }
        
        return true;
    }
    
    private function filterUrlIsValid($url)
    {
        foreach ($this->filterRegex as $rgx) {
            $r = preg_match($rgx, $url, $results);
            if ($r === 1) {
                $this->verbosePrint("'" . $url . "' matches regex and will be skipped", $rgx);
                $this->addLog('filtered', $url, 'url does not match regex');
                return false;
            }
        }

        if (!$this->filterExtensionFile($url)) {
            $this->verbosePrint('url is filtered from do not follow filters list', $url);
            return false;
        }
        
        $type = $this->getCrawler($url)->getContentType();
        
        if ($url !== $this->encodeUrl($url)) {
            $this->verbosePrint("filtered url '$url' cause of unallowed chars", $this->encodeUrl($url));
            $this->addLog('invalid_encode', $url, 'contains invalid chars');
            return false;
        }
        
        if (strpos($type, 'text/html') === false) {
            $this->verbosePrint('link "'.$url.'" is not type of content "text/html"', $type);
            $this->addLog('invalid_header', $url, 'invalid header response ' . $type);
            return false;
        }
        
        return true;
    }

    protected function encodeUrl($url)
    {
        return preg_replace("/(a-z0-9\-\#\?\=\/\.\:)/i", '', Html::encode($url));
    }

    /**
     * Saves or Updates the status for a given URL.
     *
     * If the Url does not exists as model, the model will be generated
     * Otherwise the url will be added to the crawl queue.
     *
     * @param string $url
     * @return boolean
     */
    public function urlStatus($url)
    {
        $this->verbosePrint('Inspect URL Status', $url);
        
        gc_collect_cycles();
        
        $this->verbosePrint('memory usage', memory_get_usage());
        $this->verbosePrint('memory usage peak', memory_get_peak_usage());
        
        $model = Builderindex::findUrl($this->encodeUrl($url));
        if (!$model) {
            $this->verbosePrint('found in builder index', 'no');
            // add the url to the index
            if ($this->filterUrlIsValid($url)) {
                $model = Builderindex::addToIndex($url, $this->getCrawler($url)->getTitle(), 'unknown');

                if ($model) {
                    $model->content = $this->getCrawler($url)->getContent();
                    $model->group = $this->getCrawler($url)->getGroup();
                    $model->title = $this->getCrawler($url)->getTitle();
                    $model->description = $this->getCrawler($url)->getMetaDescription();
                    $model->crawled = 1;
                    $model->status_code = 1;
                    $model->last_indexed = time();
                    $model->language_info = $this->getCrawler($url)->getLanguageInfo();
                    $model->save(false);
                }
    
                // add the pages links to the index
                foreach ($this->getCrawler($url)->getLinks() as $link) {
                    Link::add($link[1], $link[0], $url);
                    $this->verbosePrint('link iteration for new page', $link);
                    if ($this->isProcessed($link[1])) {
                        continue;
                    }
                    if ($this->matchBaseUrl($link[1])) {
                        if ($this->filterUrlIsValid($link[1])) {
                            Builderindex::addToIndex($link[1], $link[0], $url);
                        }
                    }
                }
            }
        } else {
            $this->verbosePrint('found in builder index', 'yes');
            if (!$this->filterUrlIsValid($url)) {
                $model->delete();
            } else {
                if (!$model->crawled) {
                    $model->content = $this->getCrawler($url)->getContent();
                    $model->group = $this->getCrawler($url)->getGroup();
                    $model->crawled = 1;
                    $model->status_code = 1;
                    $model->last_indexed = time();
                    $model->title = $this->getCrawler($url)->getTitle();
                    $model->description = $this->getCrawler($url)->getMetaDescription();
                    $model->language_info = $this->getCrawler($url)->getLanguageInfo();
                    $model->save(false);
                    
                    foreach ($this->getCrawler($url)->getLinks() as $link) {
                        Link::add($link[1], $link[0], $url);
                        $this->verbosePrint('link iteration for existing page', $link[1]);
                        if ($this->isProcessed($link[1])) {
                            $this->verbosePrint('link is already processed.', $link[1]);
                            continue;
                        }
                        if ($this->matchBaseUrl($link[1])) {
                            if ($this->filterUrlIsValid($link[1])) {
                                Builderindex::addToIndex($link[1], $link[0], $url);
                            }
                        }
                    }
                }
            }
        }
        
        if ($model && empty($model->content)) {
            $this->verbosePrint("Remove empty content model after crawling all links.");
            $model->delete();
        }
        unset($model);
        
        return true;
    }
}
