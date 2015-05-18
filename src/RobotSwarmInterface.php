<?php
namespace cURL\Robot;

interface RobotSwarmInterface
{
    public function add(Robot $robot);
    public function remove(Robot $robot);
    public function getRobots();

    public function getTickInterval();
    public function setTickInterval($interval);

    public function getRequestProvider();
    public function setRequestProvider(RequestProviderInterface $provider);

    public function getDefaultOptions();

    public function run();
    public function pause();
    public function isPauseRequested();
}