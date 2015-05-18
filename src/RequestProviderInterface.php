<?php
namespace cURL\Robot;

use cURL\Request;

interface RequestProviderInterface
{
    /**
     * Should return cURL\Request or FALSE when there is no more requests
     * @return Request Request object
     */
    public function nextRequest();
}
