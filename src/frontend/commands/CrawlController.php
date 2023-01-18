<?php

namespace luya\crawler\frontend\commands;

use luya\crawler\crawler\ResultHandler;
use luya\crawler\crawler\RuntimeStorage;
use luya\crawler\models\Link;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Handlers\DebugHandler;
use Nadar\Crawler\Job;
use Nadar\Crawler\Parsers\HtmlParser;
use Nadar\Crawler\Parsers\PdfParser;
use Nadar\Crawler\Runners\LoopRunner;
use Nadar\Crawler\Url;
use yii\helpers\Console;

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
 * Limit concurrency and disable pdfs.
 *
 * ```
 * ./vendor/bin/luya crawler/crawl --pdfs=0 concurrent=5
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CrawlController extends \luya\console\Command
{
    /**
     * @var boolean Whether the collected links should be checked after finished crawler process
     * @since 2.0.3
     */
    public $linkcheck = true;

    /**
     * @var boolean If linkcheck is true, the links will be added to a list. Control whether adding the link to the list should encode or not.
     * @since 3.7.0
     */
    public $encode = true;

    /**
     * @var boolean Whether PDFs should be indexed or not. When enabled this may highly increase the memory consumption of the crawler process.
     * @since 3.0
     */
    public $pdfs = true;

    /**
     *  @var integer The number of async curl requests the crawler can make, higher values may increase memory usage.
     */
    public $concurrent = 15;

    /**
     * @var boolean If enabled, the crawler can fully purge the index. This is by default disabled to prevent the issue that when the crawler 
     * starts to crawler but the target host is not returning content (maybe due to the fact its down or there is a firewall issue) the crawler
     * will finish with 0 builder index entries and override a fully available index with an empty index. Therefore this ensures that: if builder index is empty and 
     * the index is more then 0, an exception is thrown. `if ($builderIndexCount == 0 && $indexCount > 0) { Exception }`
     * @since 3.5.0
     */
    public $purging = false;

    /**
     * {@inheritDoc}
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'linkcheck';
        $options[] = 'pdfs';
        $options[] = 'concurrent';
        $options[] = 'purging';
        $options[] = 'encode';
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

        $crawler = new Crawler($this->module->baseUrl, new RuntimeStorage, new LoopRunner);
        $crawler->urlFilterRules = $this->module->filterRegex;
        $crawler->concurrentJobs = $this->concurrent;

        if ($this->verbose) {
            $debug = new DebugHandler;
            $crawler->addHandler($debug);
        }

        if ($this->pdfs) {
            $crawler->addParser(new PdfParser);
        }
        $crawler->addParser(new HtmlParser);
        $crawler->addHandler(new ResultHandler($this));
        $crawler->setup();

        foreach ($this->module->indexer as $className) {
            $indexerLinks = $className::indexLinks();
            if ($this->verbose) {
                $i = 0;
                $indexerTotal = count($indexerLinks);
                Console::startProgress(0, $indexerTotal, "indexer {$className}: ", false);
            }
            foreach ($indexerLinks as $url => $title) {
                $url = new Url($url);
                $crawler->push(new Job($url, $crawler->baseUrl));
                if ($this->verbose) {
                    $i++;
                    Console::updateProgress($i, $indexerTotal);
                }
                unset ($url);
            }

            if ($this->verbose) {
                Console::endProgress("indexer {$className} done." . PHP_EOL);
            }

            unset($indexerLinks, $indexerTotal);
        }

        $crawler->run();
        
        if ($this->linkcheck) {
            Link::cleanup($startTime);
            if ($this->verbose) {
                $i = 0;
                $total = Link::find()->select(['url'])->distinct()->count();
                Console::startProgress(0, $total, 'check links: ', false);
            }
            foreach (Link::getAllUrlsBatch() as $batch) {
                foreach ($batch as $link) {
                    $status = Link::responseStatus($link['url']);
                    Link::updateUrlStatus($link['url'], $status);
                    if ($this->verbose) {
                        $i++;
                        Console::updateProgress($i, $total);
                    }
                    unset ($status);
                }
            }
            
            if ($this->verbose) {
                Console::endProgress("done." . PHP_EOL);
            }
        }

        return $this->outputSuccess("Crawler finished.");
    }
}
