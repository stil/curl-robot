<?php
namespace cURL;

interface RequestProviderInterface
{
    /**
     * Returns cURL\Request or FALSE when there is end of available requests
     * @return cURL\Request Request object
     */
    public function nextRequest();
}
