<?php
namespace cURL;

class Robot implements RobotInterface
{
    protected $queue;
    protected $queueSize;
    protected $maximumRPM;
    protected $provider;
    protected $requestCount = 0;
    protected $timeStart = null;

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

    protected function queueNotFull()
    {
        return $this->queue->count() < $this->queueSize;
    }

    public function getCurrentRPM()
    {
        return 60 * $this->requestCount / (microtime(true) - $this->timeStart);
    }

    protected function fillQueue()
    {
        while ($this->queueNotFull() && $request = $this->requestProvider->nextRequest()) {
            $this->queue->attach($request);
        }
    }

    public function run()
    {
        $this->fillQueue();

        $this->queue->addListener('complete', function () {
            $this->requestCount++;
        }, 1000); // before default listener

        $this->queue->addListener('complete', function () {
            $this->fillQueue();
            while ($this->getCurrentRPM() > $this->maximumRPM) {
                usleep(500); // slow down, when RPM is too high
            }
        }, -1000); // after default listener

        $this->timeStart = microtime(true);
        $this->queue->send();
    }
}
