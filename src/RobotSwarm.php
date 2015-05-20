<?php
namespace cURL\Robot;

use cURL\Event;
use cURL\Request;
use cURL\RequestsQueue;
use cURL\Robot\Event\QueuePausedEvent;
use cURL\Robot\Event\RequestAttachingEvent;
use cURL\Robot\Event\RequestCompletedEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RobotSwarm extends EventDispatcher implements RobotSwarmInterface
{
    /**
     * @var Robot[]
     */
    protected $robots = [];

    /**
     * @var RequestsQueue
     */
    protected $queue;

    /**
     * @var \SplObjectStorage
     */
    protected $pendingRequests;

    /**
     * @var RequestProviderInterface
     */
    protected $requestProvider;

    /**
     * @var bool
     */
    protected $requestProviderEmpty = false;

    /**
     * @var bool
     */
    protected $pauseRequested = false;

    /**
     * @var int
     */
    protected $tickInterval = 10000;

    public function __construct()
    {
        $this->queue = new RequestsQueue();
        $this->pendingRequests = new \SplObjectStorage();
    }

    /**
     * @param Robot $robot
     */
    public function add(Robot $robot)
    {
        $this->robots[] = $robot;
    }

    /**
     * @param Robot $robot
     */
    public function remove(Robot $robot)
    {
        foreach ($this->robots as $i => $current) {
            if ($robot === $current) {
                unset($this->robots[$i]);
                break;
            }
        }
    }

    /**
     * @return Robot[]
     */
    public function getRobots()
    {
        return $this->robots;
    }

    /**
     * @return int
     */
    public function getTickInterval()
    {
        return $this->tickInterval;
    }

    /**
     * @param int $tickInterval
     */
    public function setTickInterval($tickInterval)
    {
        $this->tickInterval = $tickInterval;
    }

    /**
     * @return RequestProviderInterface
     */
    public function getRequestProvider()
    {
        return $this->requestProvider;
    }

    /**
     * @param RequestProviderInterface $requestProvider
     */
    public function setRequestProvider(RequestProviderInterface $requestProvider)
    {
        $this->requestProvider = $requestProvider;
    }

    /**
     * @return \cURL\Options
     */
    public function getDefaultOptions()
    {
        return $this->queue->getDefaultOptions();
    }

    /**
     * @return RequestHandler
     */
    protected function nextRequest()
    {
        if ($this->pendingRequests->count() > 0) {
            $this->pendingRequests->rewind();
            $handler = $this->pendingRequests->current();
            $handler->incrementAttempts();
            $this->pendingRequests->detach($handler);
        } else {
            $request = $this->requestProvider->nextRequest();
            if ($request instanceof Request) {
                $handler = new RequestHandler($request);
            } else {
                $this->requestProviderEmpty = true;
                $handler = null;
            }
        }

        return $handler;
    }

    public function run()
    {
        if (empty($this->robots)) {
            throw new \RuntimeException("No robot instances have been added. Use RobotSwarm::add() method.");
        }

        do {
            // Refill only when pause is NOT requested
            if (!$this->pauseRequested) {
                foreach ($this->robots as $robot) {
                    $this->refillRobot($robot);
                }
            }

            $queueNotEmptyBefore = $this->queue->count() > 0;
            // If queue is not empty, do socketPerform and store the result in variable
            $queueNotEmptyAfter = $queueNotEmptyBefore ? $this->queue->socketPerform() : false;

            if ($this->pauseRequested && $queueNotEmptyBefore && !$queueNotEmptyAfter) {
                $this->dispatch('queue.paused', new QueuePausedEvent($this));
                $this->pauseRequested = false;
            }

            usleep($this->tickInterval);
        } while ($queueNotEmptyAfter || !$this->requestProviderEmpty);
    }

    /**
     * @param Robot $robot
     */
    protected function refillRobot(Robot $robot)
    {
        if ($robot->rateExceeded()) {
            return;
        }

        while ($robot->queueNotFull() && ($handler = $this->nextRequest())) {
            $request = $handler->getRequest();
            $request->addListener('complete', function (Event $e) use ($handler, $robot) {
                foreach ($e->request->getListeners('complete') as $listener) {
                    $e->request->removeListener('complete', $listener);
                }

                $robot->detach();

                $event = new RequestCompletedEvent($this, $robot, $e->response, $handler);
                $this->dispatch('request.completed', $event);
                $robot->dispatch('request.completed', $event);

                if (!$this->isPauseRequested()) {
                    $this->refillRobot($robot);
                }
            });

            $event = new RequestAttachingEvent($this, $robot, $handler);
            $this->dispatch('request.attaching', $event);
            $robot->dispatch('request.attaching', $event);
            $robot->attach();
            $this->queue->attach($request);
        }
    }

    /**
     * @return bool
     */
    public function isPauseRequested()
    {
        return $this->pauseRequested;
    }

    public function pause()
    {
        $this->pauseRequested = true;
    }

    /**
     * @param RequestHandler $handler
     */
    public function retry(RequestHandler $handler)
    {
        $this->pendingRequests->attach($handler);
        $this->requestProviderEmpty = false;
    }
}