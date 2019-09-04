<?php

namespace Rublon\Core\Exceptions;

use Rublon\Core\Api\RublonAPIClient;

use Rublon\Core\Exceptions\Api\AccessTokenExpiredException;
use Rublon\Core\Exceptions\Api\APIException;
use Rublon\Core\Exceptions\Api\ApplicationDeniedException;
use Rublon\Core\Exceptions\Api\BusinessEditionLimitExceededException;
use Rublon\Core\Exceptions\Api\EmptyInputException;
use Rublon\Core\Exceptions\Api\ForbiddenMethodException;
use Rublon\Core\Exceptions\Api\InvalidJSONException;
use Rublon\Core\Exceptions\Api\InvalidSignatureException;
use Rublon\Core\Exceptions\Api\MissingFieldException;
use Rublon\Core\Exceptions\Api\MissingHeaderException;
use Rublon\Core\Exceptions\Api\PersonalEditionLimitedException;
use Rublon\Core\Exceptions\Api\SubscriptionExpiredException;
use Rublon\Core\Exceptions\Api\UnauthorizedUserException;
use Rublon\Core\Exceptions\Api\UnknownAccessTokenException;
use Rublon\Core\Exceptions\Api\UnsupportedVersionException;
use Rublon\Core\Exceptions\Api\UserBypassedException;
use Rublon\Core\Exceptions\Api\UserDeniedException;
use Rublon\Core\Exceptions\Api\UserNotFoundException;

class ApiExceptionFactory
{
    public static function createApiException($className, RublonAPIClient $client, $msg = null)
    {
        switch ($className) {
            case 'AccessTokenExpiredException':
                return new AccessTokenExpiredException($client, $msg);
            case 'ApplicationDeniedException':
                return new ApplicationDeniedException($client, $msg);
            case 'BusinessEditionLimitExceededException':
                return new BusinessEditionLimitExceededException($client, $msg);
            case 'EmptyInputException':
                return new EmptyInputException($client, $msg);
            case 'ForbiddenMethodException':
                return new ForbiddenMethodException($client, $msg);
            case 'InvalidJSONException':
                return new InvalidJSONException($client, $msg);
            case 'InvalidSignatureException':
                return new InvalidSignatureException($client, $msg);
            case 'MissingFieldException':
                return new MissingFieldException($client, $msg);
            case 'MissingHeaderException':
                return new MissingHeaderException($client, $msg);
            case 'PersonalEditionLimitedException':
                return new PersonalEditionLimitedException($client, $msg);
            case 'SubscriptionExpiredException':
                return new SubscriptionExpiredException($client, $msg);
            case 'UnauthorizedUserException':
                return new UnauthorizedUserException($client, $msg);
            case 'UnknownAccessTokenException':
                return new UnknownAccessTokenException($client, $msg);
            case 'UnsupportedVersionException':
                return new UnsupportedVersionException($client, $msg);
            case 'UserBypassedException':
                return new UserBypassedException($client, $msg);
            case 'UserDeniedException':
                return new UserDeniedException($client, $msg);
            case 'UserNotFoundException':
                return new UserNotFoundException($client, $msg);
            default:
                return new APIException($client, $msg);
        }
    }

}


