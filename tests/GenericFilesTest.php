<?php

namespace luya\crawler\tests;

use luya\testsuite\traits\MessageFileCompareTrait;
use luya\testsuite\traits\MigrationFileCheckTrait;

/**
 * Message File Compare Trait.
 * @author nadar
 *
 */
class GenericFilesTest extends CrawlerTestCase
{
    use MessageFileCompareTrait;
    use MigrationFileCheckTrait;
    
    public function testFiles()
    {
        $this->compareMessages(__DIR__ . '/../src/admin/messages', 'en');
    }

    public function testMigrations()
    {
        $this->checkMigrationFolder('@crawleradmin/migrations');
    }
}
