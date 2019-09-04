<?php

namespace Rublon\Core\Exceptions\Client;

use Rublon\Core\Api\RublonAPIClient;
use Rublon\Core\Exceptions\RublonException;

class RublonClientException extends RublonException
{
    protected $client = null;

    function __construct(RublonAPIClient $client, $msg = null, $code = 0)
    {
        parent::__construct($msg, $code);
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }
}