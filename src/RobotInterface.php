<?php
namespace cURL\Robot;

interface RobotInterface
{
    public function __construct($label = null);
    public function getLabel();

    public function getQueueSize();
    public function setQueueSize($queueSize);

    public function queueNotFull();
    public function rateExceeded();

    public function attach();
    public function detach();
}
