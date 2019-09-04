<?php
/**
 * Created by PhpStorm.
 * User: msa
 * Date: 03/07/2018
 * Time: 11:52
 */

namespace Rublon\Core\Exceptions\Api;

use Rublon\Core\Api\RublonAPIClient;
use Rublon\Core\Exceptions\RublonException;

class APIException extends RublonException
{
    protected $client = null;

    function __construct(RublonAPIClient $client, $msg = null)
    {
        parent::__construct($msg);
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }
}