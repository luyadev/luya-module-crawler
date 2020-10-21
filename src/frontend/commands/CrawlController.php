<?php

namespace luya\crawler\frontend\commands;

use luya\crawler\crawler\DatabaseStorage;
use luya\crawler\crawler\ResultHandler;
use luya\crawler\crawler\RuntimeStorage;
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
     * @var boolean Whether PDFs should be indexed or not. When enabled this may highly increase the memory consumption of the crawler process.
     * @since 3.0
     */
    public $pdfs = true;

    /**
     * {@inheritDoc}
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'linkcheck';
        $options[] = 'pdfs';
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
            foreach ($className::indexLinks() as $url => $title) {	
                $crawler->push(new Job(new Url($url), $crawler->baseUrl));
            }	
        }

        $crawler->run();
        
        if ($this->linkcheck) {
            Link::cleanup($startTime);	
            $total = Link::find()->select(['url'])->distinct()->count();
            $i = 0;
            if ($this->verbose) {
                Console::startProgress(0, $total, 'check links: ', false);    
            }
            foreach (Link::getAllUrlsBatch() as $batch) {	
                foreach ($batch as $link) {	
                    $i++;
                    $status = Link::responseStatus($link['url']);	
                    Link::updateUrlStatus($link['url'], $status);	
                    if ($this->verbose) {
                        Console::updateProgress($i, $total);
                    }
                }	
            }
            
            if ($this->verbose) {
                Console::endProgress("done." . PHP_EOL);
            }
        }

        return $this->outputSuccess("Crawler finished.");
    }
}
