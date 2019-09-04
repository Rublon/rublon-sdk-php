<?php
/**
 * Created by PhpStorm.
 * User: msa
 * Date: 03/07/2018
 * Time: 11:50
 */

namespace Rublon\Core\Exceptions\Client;

use Rublon\Core\Api\RublonAPIClient;

class MissingField_RublonClientException extends RublonClientResponseException
{
    protected $itemName;

    function __construct(RublonAPIClient $client, $itemName)
    {
        parent::__construct($client, $itemName . ' [' . get_class($this) . ']');
        $this->itemName = $itemName;
    }

    function getName()
    {
        return $this->itemName;
    }
}