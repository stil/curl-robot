<?php
namespace cURL\Robot;

class RateLimit
{
    /**
     * @var int Maximum amount of requests per time window
     */
    protected $limit;

    /**
     * @var int A time interval (in seconds)
     */
    protected $timeWindow;

    /**
     * @var double[] Timestamps of consecutive requests
     */
    protected $timestamps = [];

    /**
     * @param int $requests
     * @param int $window
     */
    public function __construct($requests, $window)
    {
        $this->limit = $requests;
        $this->timeWindow = $window;
    }

    /**
     * @return int
     */
    public function getTimeWindow()
    {
        return $this->timeWindow;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getRemaining()
    {
        return $this->limit - $this->timestampsCount();
    }

    /**
     * @param float $timestamp
     */
    public function update($timestamp)
    {
        $this->timestamps[] = $timestamp;
    }

    /**
     * @return bool
     */
    public function exceeded()
    {
        return $this->timestampsCount() >= $this->limit;
    }

    /**
     * @return int Amount of requests in time window
     */
    protected function timestampsCount()
    {
        // Removes timestamps older than time window
        $windowStart = microtime(true) - $this->timeWindow;

        $sliceIndex = false;
        foreach ($this->timestamps as $k => $v) {
            if ($v >= $windowStart) {
                $sliceIndex = $k;
                break;
            }
        }

        if ($sliceIndex === false) {
            $this->timestamps = [];
        } else if ($sliceIndex > 0) {
            $this->timestamps = array_slice($this->timestamps, $sliceIndex);
        }

        return count($this->timestamps);
    }
}