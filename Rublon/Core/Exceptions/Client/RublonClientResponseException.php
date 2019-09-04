<?php
/**
 * Created by PhpStorm.
 * User: msa
 * Date: 03/07/2018
 * Time: 11:48
 */

namespace Rublon\Core\Exceptions\Client;

use Rublon\Core\Api\RublonAPIClient;

class RublonClientResponseException extends RublonClientException
{
    function __construct(RublonAPIClient $client, $msg = null, $code = 0)
    {
        parent::__construct($client, $msg, $code);
    }
}