<?php
namespace cURL\Robot\Event;

use Symfony\Component\EventDispatcher\Event;
use cURL\Robot\RobotSwarm;

class QueuePausedEvent extends Event
{
    /**
     * @var RobotSwarm
     */
    public $swarm;

    /**
     * @param RobotSwarm $swarm
     */
    public function __construct(RobotSwarm $swarm)
    {
        $this->swarm = $swarm;
    }
}