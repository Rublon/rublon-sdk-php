<?php
/**
 * Created by PhpStorm.
 * User: msa
 * Date: 03/07/2018
 * Time: 12:00
 */

namespace Rublon\Core\Exceptions;

class RublonCallbackException extends RublonException
{

    // Defined errors
    const ERROR_MISSING_ACCESS_TOKEN = 1;
    const ERROR_REST_CREDENTIALS = 2;
    const ERROR_USER_NOT_AUTHORIZED = 5;
    const ERROR_DIFFERENT_USER = 6;
    const ERROR_API_ERROR = 7;

}