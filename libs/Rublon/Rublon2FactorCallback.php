<?php

/**
 * Class to handle the Rublon callback action.
 *
 */
class Rublon2FactorCallback {
	
	/**
	 * State GET parameter name.
	 */
	const PARAMETER_STATE = 'state';
	
	/**
	 * Access token GET parameter name.
	 */
	const PARAMETER_ACCESS_TOKEN = 'token';
	
	/**
	 * Custom URI param GET parameter name.
	 */
	const PARAMETER_CUSTOM_URI_PARAM = 'custom';
	
	/**
	 * Success state value.
	 */
	const STATE_OK = 'ok';
	
	/**
	 * Error state value.
	 */
	const STATE_ERROR = 'error';
	
	/**
	 * Logout state value.
	 */
	const STATE_LOGOUT = 'logout';
	
	const FIELD_LOGOUT_ACCESS_TOKEN = 'accessToken';
	const FIELD_LOGOUT_USER_ID = 'userId';
	const FIELD_LOGOUT_DEVICE_ID = 'deviceId';
	
	
	/**
	 * Instance of the Rublon2Factor class.
	 * 
	 * @var Rublon2Factor
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
	 * @param Rublon2Factor $rublon
	 */
	public function __construct(Rublon2Factor $rublon) {
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		$this->rublon = $rublon;
		$this->log(__METHOD__);
	}
	
	
	/**
	 * Invoke the callback.
	 * 
	 * @param callable $successHandler
	 * 			Function to handle successful authentication
	 * 			with arguments: (int) $userId, Rublon2FactorCallback $thisInstance.
	 * @param callable $cancelHandler
	 * 			Function to handle cancel request
	 * 			with argument: Rublon2FactorCallback $thisInstance.
	 * @throws RublonException
	 * 			Method may throws exception on state=error
	 * 			or other API request errors.
	 * @return void
	 */
	public function call($successHandler, $cancelHandler) {
	
		$this->successHandler = $successHandler;
		$this->cancelHandler = $cancelHandler;
		
		$state = strtolower($this->getState());
		$this->log(__METHOD__ . ' -- state=' . $state);
		
		switch ($state) {
				
			case self::STATE_OK:
				$this->handleStateOK();
				break;
	
			case self::STATE_ERROR:
				throw new RublonCallbackException('Rublon error status.', RublonCallbackException::ERROR_API_ERROR);
				break;
				
			case self::STATE_LOGOUT:
				$this->handleStateLogout();
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
	 * Handle state "OK" - run authentication.
	 *
	 * @return void
	 */
	protected function handleStateOK() {
		$this->log(__METHOD__);
		
		if ($accessToken = $this->getAccessToken()) {
			
			try /* to connect to the Rublon API and get user's ID to authenticate */ {
				$this->credentials = $this->getRublon()->getCredentials($accessToken);
			} catch (RublonException $e) {
				throw new RublonCallbackException("Rublon API credentials error.", RublonCallbackException::ERROR_REST_CREDENTIALS, $e);
			}
			
			// Authenticate user:
			$this->success($this->credentials->getUserId());
			
		} else {
			throw new RublonCallbackException("Missing access token.", RublonCallbackException::ERROR_MISSING_ACCESS_TOKEN);
		}
	
	}
	
	
	/**
	 * Finalize authentication.
	 * 
	 * @param string $userId
	 * @return void
	 */
	protected function success($userId) {
		if (!empty($this->successHandler) AND is_callable($this->successHandler)) {
			call_user_func($this->successHandler, $userId, $this);
		} else {
			trigger_error('Success handler must be a valid callback.', E_USER_ERROR);
		}
	}
	
	
	/**
	 * Handle state logout: parse input and call logout for given user.
	 * 
	 * @throws MissingField_RublonClientException
	 * @throws RublonException
	 */
	protected function handleStateLogout() {
		
		if ($input = file_get_contents("php://input")) {
			
			$message = RublonSignatureWrapper::parseMessage($input, $this->getRublon()->getSecretKey());
			$requiredFields = array(self::FIELD_LOGOUT_ACCESS_TOKEN, self::FIELD_LOGOUT_USER_ID, self::FIELD_LOGOUT_DEVICE_ID);
			foreach ($requiredFields as $field) {
				if (empty($message[$field])) {
					$response = array('status' => 'ERROR', 'msg' => 'Missing field.', 'field' => $field);
					break;
				}
			}
			
			if (empty($response)) {
				$this->handleLogout($message['userId'], $message['deviceId']);
				$response = array('status' => 'OK', 'msg' => 'Success');
			}
			
		} else {
			$response = array('status' => 'ERROR', 'msg' => 'Empty JSON input.');
		}
		
		header('content-type: application/json');
		echo json_encode($response);
		exit;
		
	}
	
	
	/**
	 * Handle logout in the local system: logout given user for given deviceId.
	 * 
	 * If you want to implement this feature, please override method in a subclass.
	 * 
	 * @param string $userId
	 * @param int $deviceId
	 */
	protected function handleLogout($userId, $deviceId) {
		// to override in a subclass
	}
	
	
	/* ---------------------------------------------------------------------------------------------------
	 * Helper methods
	 */
	
	
	/**
	 * Get Rublon instance.
	 * 
	 * @return Rublon2Factor
	 */
	protected function getRublon() {
		return $this->rublon;
	}
	
	/**
	 * Log message.
	 * 
	 * @param mixed $msg
	 * @return Rublon2FactorCallback
	 */
	protected function log($msg) {
		$this->getRublon()->log($msg);
		return $this;
	}
	
	/**
	 * Get state from GET parameters or NULL if not present.
	 *
	 * @return string|NULL
	 */
	protected function getState() {
		if (isset($_GET[self::PARAMETER_STATE])) {
			return $_GET[self::PARAMETER_STATE];
		}
	}
	
	/**
	 * Get access token from GET parameters or NULL if not present.
	 *
	 * @return string|NULL
	 */
	protected function getAccessToken() {
		if (isset($_GET[self::PARAMETER_ACCESS_TOKEN])) {
			return $_GET[self::PARAMETER_ACCESS_TOKEN];
		}
	}
	
	
	/**
	 * Get the credentials response object.
	 * 
	 * @return RublonAPICredentials
	 */
	public function getCredentials() {
		return $this->credentials;
	}
	
	
	/**
	 * Get consumer param from credentials response.
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function getConsumerParam($key) {
		if ($credentials = $this->getCredentials()) {
			$consumerParams = $credentials->getResponse();
			if (isset($consumerParams[RublonAPIClient::FIELD_RESULT]) AND isset($consumerParams[RublonAPIClient::FIELD_RESULT][$key])) {
				return $consumerParams[RublonAPIClient::FIELD_RESULT][$key];
			}
		}
	}
	

}


class RublonCallbackException extends RublonException {
	
	// Defined errors
	const ERROR_MISSING_ACCESS_TOKEN = 1;
	const ERROR_REST_CREDENTIALS = 2;
	const ERROR_USER_NOT_AUTHORIZED = 5;
	const ERROR_DIFFERENT_USER = 6;
	const ERROR_API_ERROR = 7;
	
}
