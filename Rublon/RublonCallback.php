<?php

namespace Rublon;

use Rublon\Core\Api\RublonAPIClient;
use Rublon\Core\Api\RublonAPICredentials;
use Rublon\Core\Exceptions\RublonCallbackException;
use Rublon\Core\Exceptions\RublonException;
use Rublon\Core\RublonConsumer;

/**
 * Class to handle the Rublon callback action.
 */
class RublonCallback
{

    /**
     * State GET parameter name.
     */
    const PARAMETER_STATE = 'rublonState';

    /**
     * Access token GET parameter name.
     */
    const PARAMETER_ACCESS_TOKEN = 'rublonToken';

    /**
     * Success state value.
     */
    const STATE_OK = 'ok';

    /**
     * Instance of the Rublon class.
     *
     * @var Rublon
     */
    protected $rublon;

    /**
     * Handler to finalize authentication.
     *
     * @var callable
     */
    protected $successHandler;

    /**
     * Handler on cancel.
     *
     * @var callable
     */
    protected $cancelHandler;

    /**
     * Rublon API response instance.
     *
     * @var RublonAPICredentials
     */
    protected $credentials;


    /**
     * Constructor.
     *
     * @param Rublon $rublon
     */
    public function __construct(Rublon $rublon)
    {
        if (!$rublon->isConfigured()) {
            trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
        }

        $this->rublon = $rublon;
        $this->log(__METHOD__);
    }

    /**
     * Log message.
     *
     * @param mixed $msg
     *
     * @return RublonCallback
     */
    protected function log($msg)
    {
        $this->getRublon()->log($msg);
        return $this;
    }

    /**
     * Get Rublon instance.
     *
     * @return Rublon
     */
    protected function getRublon()
    {
        return $this->rublon;
    }

    /**
     * Invoke the callback.
     *
     * @param callable $successHandler
     *            Function to handle successful authentication
     *            with arguments: (int) $appUserId, RublonCallback $thisInstance.
     * @param callable $cancelHandler
     *            Function to handle cancel request
     *            with argument: RublonCallback $thisInstance.
     *
     * @throws RublonException
     *            Method may throws exception on state=error
     *            or other API request errors.
     * @return void
     */
    public function call($successHandler, $cancelHandler)
    {
        $this->successHandler = $successHandler;
        $this->cancelHandler = $cancelHandler;

        $state = strtolower($this->getState());
        $this->log(__METHOD__ . ' -- state=' . $state);

        switch ($state) {
            case self::STATE_OK:
                $this->handleStateOK();
                break;
            default:
                if (is_callable($cancelHandler)) {
                    call_user_func($cancelHandler, $this);
                } else {
                    trigger_error('Cancel handler must be a valid callback.', E_USER_ERROR);
                }
        }

    }

    /**
     * Get state from GET parameters or NULL if not present.
     *
     * @return string|NULL
     */
    protected function getState(): ?string
    {
        return $_GET[self::PARAMETER_STATE] ?? null;
    }

    /**
     * Get access token from GET parameters or NULL if not present.
     *
     * @return string|NULL
     */
    protected function getAccessToken(): ?string
    {
        return $_GET[self::PARAMETER_ACCESS_TOKEN] ?? null;
    }

    /**
     * Handle state "OK" - run authentication.
     *
     * @throws RublonCallbackException
     */
    protected function handleStateOK()
    {
        $this->log(__METHOD__);

        if ($accessToken = $this->getAccessToken()) {
            try /* to connect to the Rublon API and get username to authenticate */ {
                $this->credentials = $this->getRublon()->getCredentials($accessToken);
            } catch (RublonException $e) {
                throw new RublonCallbackException("Rublon API credentials error.", RublonCallbackException::ERROR_REST_CREDENTIALS, $e);
            }

            // Authenticate user
            $this->success($this->credentials->getUsername());
        } else {
            throw new RublonCallbackException("Missing access token.", RublonCallbackException::ERROR_MISSING_ACCESS_TOKEN);
        }
    }

    /**
     * Finalize authentication.
     *
     * @param string $username
     * @return void
     */
    protected function success($username)
    {
        if ( ! empty($this->successHandler) && is_callable($this->successHandler)) {
            call_user_func($this->successHandler, $username, $this);
        } else {
            trigger_error('Success handler must be a valid callback.', E_USER_ERROR);
        }
    }

    /**
     * Get consumer param from credentials response.
     *
     * @param string $key
     * @return mixed
     */
    public function getConsumerParam($key)
    {
        if ($credentials = $this->getCredentials()) {
            $consumerParams = $credentials->getResponse();
            if (isset($consumerParams[RublonAPIClient::FIELD_RESULT]) && isset($consumerParams[RublonAPIClient::FIELD_RESULT][$key])) {
                return $consumerParams[RublonAPIClient::FIELD_RESULT][$key];
            }
        }
    }

    /**
     * Get the credentials response object.
     *
     * @return RublonAPICredentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
}
