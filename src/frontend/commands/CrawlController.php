<?php

namespace luya\crawler\frontend\commands;

use luya\crawler\crawler\DatabaseStorage;
use luya\crawler\crawler\ResultHandler;
use luya\crawler\models\Link;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Handlers\DebugHandler;
use Nadar\Crawler\Job;
use Nadar\Crawler\Parsers\HtmlParser;
use Nadar\Crawler\Parsers\PdfParser;
use Nadar\Crawler\Runners\LoopRunner;
use Nadar\Crawler\Storage\FileStorage;
use Nadar\Crawler\Url;
use Yii;

/**
 * Crawler console Command.
 *
 * ```sh
 * ./vendor/bin/luya crawler/crawl
 * ```
 *
 * Add verbositiy while crawling:
 *
 * ```sh
 * ./vendor/bin/luya crawler/crawl --verbose=1
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CrawlController extends \luya\console\Command
{
    public $runtimeFolder = '@runtime';

    /**
     * @var boolean Whether the collected links should be checked after finished crawler process
     * @since 2.0.3
     */
    public $linkcheck = true;

    /**
     * @var boolean Whether a table based summary should be rendered.
     * @since 2.0.3
     */
    public $summary = true;

    /**
     * {@inheritDoc}
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'linkcheck';
        $options[] = 'summary';
        return $options;
    }

    /**
     * Start the crawler command.
     *
     * @return integer
     */
    public function actionIndex()
    {
        $startTime = time();

        $crawler = new Crawler($this->module->baseUrl, new FileStorage(Yii::getAlias($this->runtimeFolder)), new LoopRunner);
        $crawler->urlFilterRules = $this->module->filterRegex;

        if ($this->verbose) {
            $debug = new DebugHandler;
            $crawler->addHandler($debug);
        }

        $crawler->addParser(new PdfParser);
        $crawler->addParser(new HtmlParser);
        $crawler->addHandler(new ResultHandler($this));
        $crawler->setup();

        foreach ($this->module->indexer as $className) {	
            foreach ($className::indexLinks() as $url => $title) {	
                $crawler->push(new Job(new Url($url), $crawler->baseUrl));
            }	
        }

        $crawler->run();
        
        if ($this->linkcheck) {	          
            Link::cleanup($startTime);	
            foreach (Link::getAllUrlsBatch() as $batch) {	
                foreach ($batch as $link) {	
                    $status = Link::responseStatus($link['url']);	
                    Link::updateUrlStatus($link['url'], $status);	
                }	
            }	
        }

        return $this->outputSuccess("Crawler finished.");
    }
}
