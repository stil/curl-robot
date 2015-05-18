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
     * @var int Maximum amount of requests per minute
     */
    protected $maximumRPM;

    /**
     * @var int Amount of requests currently processed in queue
     */
    protected $attachedRequests = 0;

    /**
     * @var double[] Timestamps of consecutive requests
     */
    protected $requestTimestamps = [];

    /**
     * @var int How many last requests will be used to calculate RPM
     */
    protected $speedMeterFrame = 16;

    /**
     * @var int Current size of $requestsTimestamps array
     */
    protected $currentSpeedFrame = 0;

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
    public function getMaximumRPM()
    {
        return $this->maximumRPM;
    }

    /**
     * @param $rpm
     */
    public function setMaximumRPM($rpm)
    {
        $this->maximumRPM = $rpm;
    }

    /**
     * @return int
     */
    public function getSpeedMeterFrame()
    {
        return $this->speedMeterFrame;
    }

    /**
     * @param int $speedMeterFrame
     */
    public function setSpeedMeterFrame($speedMeterFrame)
    {
        $this->speedMeterFrame = $speedMeterFrame;
    }

    /**
     * @return bool
     */
    public function queueNotFull()
    {
        return $this->attachedRequests < $this->queueSize;
    }

    /**
     * @return float|null
     */
    public function getCurrentRPM()
    {
        if (empty($this->requestTimestamps)) {
            return null;
        }

        return 60 * $this->currentSpeedFrame / (microtime(true) - $this->requestTimestamps[0]);
    }

    /**
     * @return bool
     */
    public function speedExceeded()
    {
        return $this->getCurrentRPM() > $this->maximumRPM;
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

        if (count($this->requestTimestamps) >= $this->speedMeterFrame) {
            array_shift($this->requestTimestamps);
        } else {
            $this->currentSpeedFrame++;
        }

        $this->requestTimestamps[] = microtime(true);
    }
}
