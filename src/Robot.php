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
     * @var int A time interval (in seconds) which is used to calculate current RPM
     */
    protected $speedMeterWindow = 10;

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
    public function getSpeedMeterWindow()
    {
        return $this->speedMeterWindow;
    }

    /**
     * @param int $speedMeterWindow
     */
    public function setSpeedMeterWindow($speedMeterWindow)
    {
        $this->speedMeterWindow = $speedMeterWindow;
    }

    /**
     * @return bool
     */
    public function queueNotFull()
    {
        return $this->attachedRequests < $this->queueSize;
    }

    /**
     * @return float
     */
    public function getCurrentRPM()
    {
        $this->timestampsCleanup();
        return 60 * count($this->requestTimestamps) / $this->speedMeterWindow;
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
        $this->requestTimestamps[] = microtime(true);
    }

    /**
     * Removes timestamps older than speed meter window
     */
    protected function timestampsCleanup()
    {
        $windowStart = microtime(true) - $this->speedMeterWindow;

        $sliceIndex = false;
        foreach ($this->requestTimestamps as $k => $v) {
            if ($v >= $windowStart) {
                $sliceIndex = $k;
                break;
            }
        }

        if ($sliceIndex === false) {
            $this->requestTimestamps = [];
        } else if ($sliceIndex > 0) {
            $this->requestTimestamps = array_slice($this->requestTimestamps, $sliceIndex);
        }
    }
}
