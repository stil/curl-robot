<?php
namespace cURL\Robot\Event;

use Symfony\Component\EventDispatcher\Event;
use cURL\Request;
use cURL\Response;
use cURL\Robot\Robot;
use cURL\Robot\RobotSwarm;
use cURL\Robot\RequestHandler;

class RequestCompletedEvent extends Event
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
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var RequestHandler
     */
    public $handler;


    public function __construct(RobotSwarm $swarm, Robot $robot, Response $response, RequestHandler $handler)
    {
        $this->swarm = $swarm;
        $this->robot = $robot;
        $this->handler = $handler;
        $this->request = $handler->getRequest();
        $this->response = $response;
    }
}