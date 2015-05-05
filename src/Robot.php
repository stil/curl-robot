<?php
namespace cURL;

class Robot implements RobotInterface
{
    /**
     * @var RequestsQueue
     */
    protected $queue;

    /**
     * @var int Maximum size of queue
     */
    protected $queueSize;

    /**
     * @var int Maximum amount of requests per minute
     */
    protected $maximumRPM;

    /**
     * @var RequestProviderInterface
     */
    protected $requestProvider;

    /**
     * @var double[] Timestamps of consecutive requests
     */
    protected $requestTimestamps = [];

    /**
     * @var int How many last requests will be used to calculate RPM
     */
    protected $speedMeterFrame = 16;

    /**
     * @var float Unix timestamp of queue execution start
     */
    protected $timeStart = null;

    protected $pauseRequested = false;

    public function __construct()
    {
        $this->queue = new RequestsQueue();
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setRequestProvider(RequestProviderInterface $provider)
    {
        $this->requestProvider = $provider;
    }

    public function setQueueSize($size)
    {
        $this->queueSize = $size;
    }

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

    protected function queueNotFull()
    {
        return $this->queue->count() < $this->queueSize;
    }

    public function getCurrentRPM()
    {
        if (empty($this->requestTimestamps)) {
            return null;
        }

        return 60 * count($this->requestTimestamps) / (microtime(true) - $this->requestTimestamps[0]);
    }

    /**
     * Pauses execution to make RPM lower than maximum RPM
     */
    public function regulateRPM()
    {
        if ($this->getCurrentRPM() > $this->maximumRPM) {
            $sleep = (60 * count($this->requestTimestamps) / $this->maximumRPM) + $this->requestTimestamps[0] - microtime(true);
            $sleep *= 1000000;
            if ($sleep > 0) {
                usleep($sleep);
            }
        }
    }

    protected function fillQueue()
    {
        while (!$this->pauseRequested && $this->queueNotFull() && $request = $this->requestProvider->nextRequest()) {
            $this->queue->attach($request);
        }
    }

    public function run()
    {
        $this->fillQueue();

        $this->queue->addListener('complete', function () {
            $this->requestTimestamps[] = microtime(true);
            if (count($this->requestTimestamps) > $this->speedMeterFrame) {
                array_shift($this->requestTimestamps);
            }
        }, 1000); // before default listener

        $this->queue->addListener('complete', function () {
            $this->fillQueue();
            $this->regulateRPM();
        }, -1000); // after default listener

        $this->timeStart = microtime(true);
        $this->queue->send();
    }

    public function hasPaused()
    {
        return $this->pauseRequested && $this->queue->count() == 0;
    }

    public function isPauseRequested()
    {
        return $this->pauseRequested;
    }

    public function pause()
    {
        $this->pauseRequested = true;
    }

    public function resume()
    {
        $this->pauseRequested = false;
    }
}
