<?php

namespace luya\crawler\frontend\commands;

use luya\crawler\crawler\DatabaseStorage;
use luya\crawler\crawler\ResultHandler;
use Nadar\Crawler\Crawler;
use Nadar\Crawler\Handlers\DebugHandler;
use Nadar\Crawler\Parsers\HtmlParser;
use Nadar\Crawler\Parsers\PdfParser;
use Nadar\Crawler\Runners\LoopRunner;

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
        $crawler = new Crawler($this->module->baseUrl, new DatabaseStorage, new LoopRunner);
        $crawler->urlFilterRules = $this->module->filterRegex;

        if ($this->verbose) {
            $debug = new DebugHandler;
            $crawler->addHandler($debug);
        }

        $crawler->addParser(new PdfParser);
        $crawler->addParser(new HtmlParser);
        $crawler->addHandler(new ResultHandler);
        $crawler->setup();
        $crawler->run();

        if ($this->verbose) {
            $debug->summary();
        }
    }
}
