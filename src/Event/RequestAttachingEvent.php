<?php
namespace cURL\Robot\Event;

use Symfony\Component\EventDispatcher\Event;
use cURL\Request;
use cURL\Robot\RobotSwarm;
use cURL\Robot\Robot;
use cURL\Robot\RequestHandler;

class RequestAttachingEvent extends Event
{
    /**
     * @var RobotSwarm
     */
    public $swarm;

    /**
     * @var Robot
     */
    public $robot;

    /**
     * @var RequestHandler
     */
    public $handler;

    /**
     * @var Request
     */
    public $request;

    /**
     * @param RobotSwarm $swarm
     * @param Robot $robot
     * @param RequestHandler $handler
     */
    public function __construct(RobotSwarm $swarm, Robot $robot, RequestHandler $handler)
    {
        $this->swarm = $swarm;
        $this->robot = $robot;
        $this->request = $handler->getRequest();
        $this->handler = $handler;
    }
}