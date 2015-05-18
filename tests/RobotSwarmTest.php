<?php
namespace cURL\Robot\Tests;

use cURL\Robot\Robot;
use cURL\Robot\RobotSwarm;

class RobotSwarmTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $swarm = new RobotSwarm();
        $this->assertEmpty($swarm->getRobots());
        $robot = new Robot();
        $swarm->add($robot);
        $this->assertCount(1, $swarm->getRobots());
        $this->assertEquals($robot, $swarm->getRobots()[0]);
    }

    public function testRemove()
    {
        $swarm = new RobotSwarm();
        $robot1 = new Robot();
        $robot2 = new Robot();
        $swarm->add($robot1);
        $swarm->add($robot2);
        $swarm->remove($robot1);
        $this->assertCount(1, $swarm->getRobots());
        $swarm->remove($robot2);
        $this->assertEmpty($swarm->getRobots());
    }
}
