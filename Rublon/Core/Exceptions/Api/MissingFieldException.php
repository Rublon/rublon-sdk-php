<?php
/**
 * Created by PhpStorm.
 * User: msa
 * Date: 03/07/2018
 * Time: 11:53
 */

namespace Rublon\Core\Exceptions\Api;

use Rublon\Core\Api\RublonAPIClient;

class MissingFieldException extends APIException
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