<?php

namespace luya\crawler\frontend\commands;

use Yii;
use luya\console\Command;
use luya\crawler\models\Builderindex;
use luya\crawler\models\Link;
use yii\console\widgets\Table;

/**
 * Check brocken links.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.3
 */
class LinkController extends Command
{
    /**
     * Run the clean up and update link status methods.
     * 
     * @return integer
     */
    public function actionIndex()
    {
        // remove all links which are older then the oldest crawler_builder last_index timestamp
        $min = Builderindex::find()->min('last_indexed');
        $this->verbosePrint("Remove links older then " . Yii::$app->formatter->asDate($min));

        Link::cleanup($min);

        $this->verbosePrint("Check the status of all links.");

        $log = [];
        foreach (Link::getAllUrlsBatch() as $batch) {
            foreach ($batch as $link) {
                $this->verbosePrint("start check", $link['url']);
                $status = Link::responseStatus($link['url']);
                $this->verbosePrint($status, $link['url']);

                $log[] = [$link['url'], $status];
                Link::updateUrlStatus($link['url'], $status);
            }
        }

        $table = new Table();
        $table->setHeaders(['url', 'status']);
        $table->setRows($log);
        return $this->outputSuccess("Run finished.");
    }
}