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

        $this->assertEquals(0, $robot->getCurrentRPM());
        $robot->attach();
        $this->assertEquals(0, $robot->getCurrentRPM());
        $robot->detach();
        $this->assertGreaterThan(0, $robot->getCurrentRPM());

        $robot = new Robot('test');
        $robot->setSpeedMeterWindow(1);

        $robot->attach();
        $robot->detach();
        $this->assertEquals(60, $robot->getCurrentRPM());
        sleep(1);
        $this->assertEquals(0, $robot->getCurrentRPM());

        $robot->attach();
        $robot->detach();
        $this->assertEquals(60, $robot->getCurrentRPM());

        $robot->attach();
        $robot->detach();
        $this->assertEquals(120, $robot->getCurrentRPM());
    }

    public function testSpeedExceeded()
    {
        $robot = new Robot('test');
        $robot->setMaximumRPM(20);
        $robot->setSpeedMeterWindow(1);
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
