<?php
namespace cURL\Robot\Tests;

use cURL\Robot\RateLimit;

class RateLimitTest extends \PHPUnit_Framework_TestCase
{

    public function testExceeded()
    {
        // Maximum 2 requests per 1 second
        $limit = new RateLimit(2, 1);

        $this->assertFalse($limit->exceeded()); // 0 requests

        $limit->update(microtime(true));
        $this->assertFalse($limit->exceeded()); // 1 request

        $limit->update(microtime(true));
        $this->assertTrue($limit->exceeded()); // 2 requests

        sleep(1);
        $this->assertFalse($limit->exceeded()); // 2 requests
    }

    public function testGetRemaining()
    {
        $limit = new RateLimit(20, 1);
        for ($i = 20; $i >= 0; $i--) {
            $this->assertEquals($i, $limit->getRemaining());
            $limit->update(microtime(true));
        }
        sleep(1);
        $this->assertEquals(20, $limit->getRemaining());
    }

    public function testConstruct()
    {
        $limit = new RateLimit(16, 32);
        $this->assertEquals(16, $limit->getLimit());
        $this->assertEquals(32, $limit->getTimeWindow());
    }
}
