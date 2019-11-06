<?php

namespace luya\crawler\frontend\commands;

use luya\console\Command;
use luya\crawler\models\Link;

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
        $this->verbosePrint("Check the status of all links.");
        Link::updateLinkStatus();

        return $this->outputSuccess("Run finished.");
    }
}