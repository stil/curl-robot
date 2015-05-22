<?php
namespace cURL\Robot\Tests;

use cURL\Robot\RateLimit;
use cURL\Robot\Robot;

class RobotTest extends \PHPUnit_Framework_TestCase
{
    public function testLabel()
    {
        $id = 'testid';
        $robot = new Robot($id);
        $this->assertEquals($id, $robot->getLabel());
    }

    public function testQueueNotFull()
    {
        $robot = new Robot();
        $robot->setQueueSize(3);

        $this->assertTrue($robot->queueNotFull());
        $robot->attach();

        $this->assertTrue($robot->queueNotFull());
        $robot->attach();

        $this->assertTrue($robot->queueNotFull());
        $robot->attach();

        $this->assertFalse($robot->queueNotFull());
        $robot->detach();

        $this->assertTrue($robot->queueNotFull());
    }

    public function testRateExceeded()
    {
        $robot = new Robot();
        $robot->addRateLimit(new RateLimit(1, 1));
        $robot->addRateLimit(new RateLimit(5, 10));

        // Add 5 requests one after another every second
        for ($i = 0; $i < 4; $i++) {
            $robot->attach();
            $robot->detach();
            $this->assertTrue($robot->rateExceeded());
            sleep(1);
            $this->assertFalse($robot->rateExceeded());
        }

        // Add 6th request
        $robot->attach();
        $robot->detach();
        sleep(1);
        // Exceeded second rate limit 5 reqs / 10s
        $this->assertTrue($robot->rateExceeded());
    }
}
