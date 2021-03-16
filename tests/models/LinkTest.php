<?php

namespace luya\crawler\tests\models;

use luya\crawler\models\Link;
use luya\crawler\tests\CrawlerTestCase;
use luya\testsuite\fixtures\NgRestModelFixture;

class LinkTest extends CrawlerTestCase
{
    public function testHeadRequestStatusCode()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Link::class,
        ]);

        $status = Link::responseStatus('https://luya.io');

        $this->assertSame(200, $status);

        $this->assertSame(404, Link::responseStatus('https://luya.io/not-found'));
        $this->assertSame(-1, Link::responseStatus('https://thisurlshouldreallynotexiststotest'));
    }
}
