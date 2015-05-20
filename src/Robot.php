<?php
namespace cURL\Robot;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Robot extends EventDispatcher implements RobotInterface
{
    /**
     * @var int|string
     */
    protected $label;

    /**
     * @var int Maximum size of queue
     */
    protected $queueSize;

    /**
     * @var int
     */
    protected $totalCompletedRequests = 0;

    /**
     * @var int Amount of requests currently processed in queue
     */
    protected $attachedRequests = 0;

    /**
     * @var RateLimit[]
     */
    protected $rateLimits = [];

    /**
     * @param int|string $label Label of this Robot instance. May be used for debugging.
     */
    public function __construct($label = null)
    {
        $this->label = $label;
    }

    /**
     * @return int|string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getQueueSize()
    {
        return $this->queueSize;
    }

    /**
     * @param int $queueSize
     */
    public function setQueueSize($queueSize)
    {
        $this->queueSize = $queueSize;
    }

    /**
     * @return int
     */
    public function getTotalCompletedRequests()
    {
        return $this->totalCompletedRequests;
    }

    /**
     * @param RateLimit $limit
     */
    public function addRateLimit(RateLimit $limit)
    {
        $this->rateLimits[] = $limit;
    }

    /**
     * @return bool
     */
    public function queueNotFull()
    {
        return $this->attachedRequests < $this->queueSize;
    }

    /**
     * @return bool
     */
    public function rateExceeded()
    {
        foreach ($this->rateLimits as $limit) {
            if ($limit->exceeded()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Increases amount of currently processed requests by one
     */
    public function attach()
    {
        $this->attachedRequests++;
    }

    /**
     * Decreases amount of currently processed requests by one and updates speed meter
     */
    public function detach()
    {
        $this->attachedRequests--;
        $this->totalCompletedRequests++;

        $now = microtime(true);
        foreach ($this->rateLimits as $limit) {
            $limit->update($now);
        }
    }
}
