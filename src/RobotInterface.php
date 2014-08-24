<?php
namespace cURL;

interface RobotInterface
{
    /**
     * Sets callback function which returns cURL\Request or FALSE
     * @param RequestProviderInterface $provider Callback function
     */
    public function setRequestProvider(RequestProviderInterface $provider);

    /**
     * Sets maximum amount of requests executed at once
     * @param integer $size Queue size
     */
    public function setQueueSize($size);

    /**
     * Returns instance of cURL\RequestsQueue
     * @return RequestsQueue Instance of queue
     */
    public function getQueue();

    /**
     * Sets maximum processing speed in requests per minute
     * @param integer $rpm Maximum requests per minute value
     */
    public function setMaximumRPM($rpm);

    /**
     * Returns current requests per minute speed
     * @return integer Current requests per minute speed
     */
    public function getCurrentRPM();

    /**
     * Starts execution of requests
     * @return void
     */
    public function run();
}
