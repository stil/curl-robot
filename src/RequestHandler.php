<?php
namespace cURL\Robot;

use cURL\Request;

class RequestHandler
{
    /**
     * @var int
     */
    protected $attempts = 1;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Increases amount of attempts by one
     */
    public function incrementAttempts()
    {
        $this->attempts++;
    }
}