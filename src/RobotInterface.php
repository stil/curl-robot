<?php
namespace cURL\Robot;

interface RobotInterface
{
    public function __construct($label = null);
    public function getLabel();

    public function getQueueSize();
    public function setQueueSize($queueSize);

    public function getMaximumRPM();
    public function setMaximumRPM($rpm);

    public function getSpeedMeterWindow();
    public function setSpeedMeterWindow($window);

    public function queueNotFull();
    public function getCurrentRPM();
    public function speedExceeded();

    public function attach();
    public function detach();
}
