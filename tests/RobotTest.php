<?php
namespace cURL\Robot\Tests;

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
        $robot = new Robot('test');
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

    public function testGetCurrentRPM()
    {
        $robot = new Robot('test');

        $this->assertNull($robot->getCurrentRPM());
        $robot->attach();
        $this->assertNull($robot->getCurrentRPM());
        $robot->detach();
        $this->assertGreaterThan(0, $robot->getCurrentRPM());

        $robot = new Robot('test');
        $robot->attach();
        $robot->detach();
        sleep(2);

        // 1 req / 2s ~ 30 RPM
        $this->assertLessThan(0.5, abs($robot->getCurrentRPM() - 30));
        $robot->attach();
        $robot->detach();

        // 2 reqs / 2s ~ 60 RPM
        $this->assertLessThan(0.5, abs($robot->getCurrentRPM() - 60));
        sleep(1);

        // 2 reqs / 3s ~ 40 RPM
        $this->assertLessThan(0.5, abs($robot->getCurrentRPM() - 40));
    }

    public function testSpeedExceeded()
    {
        $robot = new Robot('test');
        $robot->setMaximumRPM(60);
        $this->assertFalse($robot->speedExceeded());
        $robot->attach();
        $robot->detach();
        $this->assertTrue($robot->speedExceeded());
        usleep(500000);
        $this->assertTrue($robot->speedExceeded());
        sleep(1);
        $this->assertFalse($robot->speedExceeded());
    }
}
