<?php

namespace crawlerests;

use luya\testsuite\traits\MessageFileCompareTrait;

/**
 * Message File Compare Trait.
 * @author nadar
 *
 */
class MessageFileTest extends CrawlerTestCase
{
    use MessageFileCompareTrait;
    
    public function testFiles()
    {
        $this->compareMessages(__DIR__ . '/../src/admin/messages', 'en');
    }
}
