<?php

namespace luya\crawler\frontend\commands;

use luya\crawler\frontend\classes\CrawlContainer;
use luya\crawler\models\Link;
use luya\helpers\FileHelper;
use yii\console\widgets\Table;

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
    public $linkCheck = true;

    /**
     * {@inheritDoc}
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'linkCheck';
        return $options;
    }

    /**
     * Start the crawler command.
     *
     * @return integer
     */
    public function actionIndex()
    {
        $this->verbosePrint(var_export($this->linkCheck), 'option link check');

        // sart time measuremnt
        $start = microtime(true);
        
        $container = new CrawlContainer([
            'linkCheckIndexInternalUrls' => $this->linkCheck,
            'baseUrl' => $this->module->baseUrl,
            'filterRegex' => $this->module->filterRegex,
            'verbose' => $this->verbose,
            'doNotFollowExtensions' => $this->module->doNotFollowExtensions,
            'useH1' => $this->module->useH1,
        ]);
        
        foreach ($this->module->indexer as $className) {
            foreach ($className::indexLinks() as $url => $title) {
                $container->addToIndex($url, $title, $className);
            }
        }

        $container->start();

        $this->verbosePrint(var_export($this->linkCheck), 'link check');

        if ($this->linkCheck) {
            $this->verbosePrint("Start link check");
            Link::cleanup($container->startTime);
            foreach (Link::getAllUrlsBatch() as $batch) {
                foreach ($batch as $link) {
                    $this->verbosePrint("start check", $link['url']);
                    $status = Link::responseStatus($link['url']);
                    $this->verbosePrint($status, $link['url']);
                    Link::updateUrlStatus($link['url'], $status);
                }
            }
        }

        $this->verbosePrint("start table output");

        $timeElapsed = round((microtime(true) - $start) / 60, 2);
        
        $table = new Table();
        $table->setHeaders(['status', 'url', 'message']);
        $table->setRows($container->getReport());
        $this->output($table->run());
        $this->outputInfo('memory usage: ' . FileHelper::humanReadableFilesize(memory_get_usage()));
        $this->outputInfo('memory peak usage: ' . FileHelper::humanReadableFilesize(memory_get_peak_usage()));
        
        return $this->outputSuccess('Crawler finished in ' . $timeElapsed . ' min.');
    }
}
