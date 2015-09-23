<?php

/**
 * Parameters wrapper of the Rublon authentication process.
 * 
 * This class is used to prepare the parameters for the authentication
 * process. This includes both the parameters used for the authentication
 * itself as well as any additional parameters that would be used by the
 * integrated website in the callback. An object of this class can also
 * be used to embed the authentication parameters in a Rublon button. 
 * 
 * @see RublonButton
 */
class RublonAuthParams {
	
	/**
	 * Hash algorithm name to compute the user's email hash.
	 */
	const HASH_ALG = 'sha256';

	/**
	 * Rublon instance.
	 * 
	 * An istance of the Rublon class. Necessary for
	 * the class to work.
	 *
	 * @var Rublon
	 */
	protected $rublon = null;
	
	/**
	 * Consumer parameters store.
	 * 
	 * These optional parameters can be set by the integrated website.
	 * They will be signed with the Signature Wrapper (RublonSignatureWrapper class)
	 * using the website's secret key and can be retrieved in the callback via
	 * the getResponse() method of the RublonAPIClient class. 
	 * 
	 * @var array
	 */
	protected $consumerParams = array();
	
	/**
	 * Action flag.
	 * 
	 * @var string
	 */
	protected $actionFlag;
	

	/**
	 * Field name for access token parameter
	 */
	const FIELD_ACCESS_TOKEN = "accessToken";

	/**
	 * Name of the field with profile ID.
	 */
	const FIELD_PROFILE_ID = 'profileId';
	
	/**
	 * Field name for "service" parameter
	 */
	const FIELD_SERVICE = "service";
	
	/**
	 * Field name for "systemToken" parameter
	 */
	const FIELD_SYSTEM_TOKEN = "systemToken";
	
	/**
	 * Field name for origin URL.
	 */
	const FIELD_ORIGIN_URL = "originUrl";
	
	/**
	 * Field name for language parameter
	 */
	const FIELD_LANG = "lang";
	
	/**
	 * Field name for window type.
	 */
	const FIELD_WINDOW_TYPE = 'windowType';
	
	/**
	 * Field name for return URL.
	 */
	const FIELD_RETURN_URL = 'returnUrl';
	
	/**
	 * Field name for consumer parameters
	 */
	const FIELD_CONSUMER_PARAMS = "consumerParams";
	
	/**
	 * Field name for callback URL.
	 */
	const FIELD_CALLBACK_URL = 'callbackUrl';
	
	/**
	 * Field name for local user ID.
	 */
	const FIELD_USER_ID = "userId";

	/**
	 * Field name for local user email address.
	 */
	const FIELD_USER_EMAIL_HASH = "userEmailHash";
	
	/**
	 * Field name for logout listener boolean flag.
	 */
	const FIELD_LOGOUT_LISTENER = "logoutListener";

	/**
	 * Field name for required Rublon user's profile ID.
	 */
	const FIELD_REQUIRE_PROFILE_ID = "requireProfileId";

	/**
	 * Field name for action flag.
	 */
	const FIELD_ACTION_FLAG = "actionFlag";
	
	/**
	 * Field name for version parameter.
	 */
	const FIELD_VERSION = "version";

	/**
	 * Field name to require Rublon to authenticate
	 * by mobile app only, not using Email 2-factor.
	 */
	const FIELD_FORCE_MOBILE_APP = 'forceMobileApp';
	
	/**
	 * Field name to force ignoring the existing Trusted Device
	 * during the authentication.
	 */
	const FIELD_IGNORE_TRUSTED_DEVICE = 'ignoreTrustedDevice';
	
	/**
	 * Field name to add a custom URI query parameter to the callback URL.
	 */
	const FIELD_CUSTOM_URI_PARAM = 'customURIParam';
	
	/**
	 * Field name to define a message for a transaction.
	 */
	const FIELD_CONFIRM_MESSAGE = 'confirmMessage';
	
	/**
	 * Field name to set the time buffer in seconds from previous confirmation
	 * which allow Rublon to confirm the custom transaction
	 * without user's action.
	 */
	const FIELD_CONFIRM_TIME_BUFFER = 'confirmTimeBuffer';
	
	/**
	 * URL path to authentication code
	 */
	const URL_PATH_CODE = "/code/native/";
	
	/**
	 * Action flag for login action.
	 */
	const ACTION_FLAG_LOGIN = 'login';
	
	/**
	 * Action flag for enable protection action.
	 */
	const ACTION_FLAG_LINK_ACCOUNTS = 'link_accounts';
	
	/**
	 * Action flag for disable protection action.
	 */
	const ACTION_FLAG_UNLINK_ACCOUNTS = 'unlink_accounts';
	

	/**
	 * Initialize object with Rublon instance.
	 * 
	 * A Rublon class instance is required for
	 * the object to work.
	 *
	 * @param RublonConsumer $rublon An instance of the Rublon class
	 */
	public function __construct(RublonConsumer $rublon) {
		$rublon->log(__METHOD__);
		$this->rublon = $rublon;
	}
	
	
	/**
	 * Get URL of the authentication request to perform simple HTTP redirection.
	 * 
	 * Returns a URL address that will start the Rublon
	 * authentication process if redirected to.
	 * 
	 * @return string URL address
	 */
	public function getUrl() {
		$this->getRublon()->log(__METHOD__);
		return $this->getRublon()->getAPIDomain() .
			self::URL_PATH_CODE .
			urlencode($this->getUrlParamsString());
	}
	
	
	
	/**
	 * Get parameters string to apply in the authentication URL address.
	 * 
	 * Returns the authentication parameters as a base64-encoded JSON string
	 * that will be passed with the URL address to the Rublon code window.
	 * 
	 * @return string
	 */
	protected function getUrlParamsString() {
		return base64_encode(json_encode($this->getUrlParams()));
	}
	
	
	/**
	 * Get ready-made authentication parameters object to apply in the authentication URL address.
	 * 
	 * Returns the authentication process parameters as an object
	 * (including the Signature Wrapper-signed consumer params)
	 * that will be passed with the URL address to the Rublon code window. 
	 * 
	 * @return array
	 */
	public function getUrlParams() {
		
		$consumerParams = $this->getConsumerParams();
		$params = array();
		
		if (!empty($consumerParams)) {
			$wrapper = RublonSignatureWrapper::wrap(
				$this->getRublon()->getSecretKey(),
				$consumerParams
			);
			$params[self::FIELD_CONSUMER_PARAMS] = $wrapper;
		}
		
		return $params;
		
	}
	
	/**
	 * Get the consumer parameters wrapper to apply in the Rublon button.
	 * 
	 * Returns the Signature Wrapper-signed consumer params
	 * to apply in the HTML wrapper of the Rublon button.
	 *
	 * @return array|NULL
	 */
	public function getConsumerParamsWrapper() {
		$consumerParams = $this->getConsumerParams();
		
		if (!empty($consumerParams)) {
			return RublonSignatureWrapper::wrap(
				$this->getRublon()->getSecretKey(),
				$consumerParams
			);
		} else {
			return null;
		}
	}
	
	
	/**
	 * Get the consumer parameters string to apply in the Rublon button.
	 * 
	 * Returns the Signature Wrapped-signed consumer params
	 * as a JSON string.
	 * 
	 * @return string|NULL
	 */
	public function getConsumerParamsWrapperString() {
		return json_encode($this->getConsumerParamsWrapper());
	}
	
	
	/**
	 * Set action flag.
	 *
	 * @param string $actionFlag
	 * @return RublonAuthParams
	 */
	public function setActionFlag($actionFlag) {
		$this->actionFlag = $actionFlag;
		return $this;
	}
	
	/**
	 * Get action flag.
	 *
	 * @return string
	 */
	public function getActionFlag() {
		return $this->actionFlag;
	}
	
	
	/**
	 * Set consumer parameters.
	 *
	 * Sets the consumer parameters using the given array.
	 *
	 * @param array $consumerParams An array of consumer parameters
	 * @return RublonAuthParams
	 */
	public function setConsumerParams($consumerParams) {
		$this->consumerParams = $consumerParams;
		return $this;
	}
	
	/**
	 * Set single consumer parameter.
	 * 
	 * Allows to add a single consumer param to the consumer
	 * params array.
	 * 
	 * @param string $name Param key in the array.
	 * @param mixed $value Param value.
	 * @return RublonAuthParams
	 */
	public function setConsumerParam($name, $value) {
		$this->consumerParams[$name] = $value;
		return $this;
	}
	

	/**
	 * Get consumer parameters.
	 *
	 * @return array
	 */
	public function getConsumerParams() {
		
		$consumerParams = $this->consumerParams;
		
		// Now set some default required parameters. 
		
		// Service name:
		if ($serviceName = $this->getRublon()->getServiceName()) {
			$consumerParams[self::FIELD_SERVICE] = $serviceName;
		}
		
		// Language code:
		if ($lang = $this->getRublon()->getLang()) {
			$consumerParams[self::FIELD_LANG] = $lang;
		}

		// Action flag:
		if ($actionFlag = $this->getActionFlag()) {
			$consumerParams[self::FIELD_ACTION_FLAG] = $actionFlag;
		}
		
		// Consumer's system token:
		$consumerParams[self::FIELD_SYSTEM_TOKEN] = $this->getRublon()->getSystemToken();
		
		// Protocol version:
		$consumerParams[self::FIELD_VERSION] = $this->getRublon()->getVersionDate();
		
		return $consumerParams;
		
	}
	
	
	/**
	 * Get single consumer parameter.
	 * 
	 * Returns a single consumer param from the consumer params
	 * array or null if the requested param doesn't exist.
	 * 
	 * @param string $name Param key in the array.
	 * @return mixed|NULL
	 */
	public function getConsumerParam($name) {
		$consumerParams = $this->getConsumerParams();
		if (isset($consumerParams[$name])) {
			return $consumerParams[$name];
		} else {
			return NULL;
		}
	}
	
	
	/**
	 * Get Rublon instance.
	 * 
	 * @return Rublon
	 */
	public function getRublon() {
		return $this->rublon;
	}
	

	/**
	 * Create instance of the RublonAuthParams by given configuration.
	 *
	 * @param RublonConsumer $rublon
	 * @param string $callbackUrl Callback URL address.
	 * @param array $params Existing instance of the RublonAuthParams to configure or consumer parameters array (optional)
	 * @return RublonAuthParams
	 */
	static public function initAuthParams(RublonConsumer $rublon, $callbackUrl, $params = null) {
		if (is_object($params) AND $params instanceof RublonAuthParams) {
			$authParams = $params;
		} else {
			$authParams = new RublonAuthParams($rublon);
			if (is_array($params) AND !empty($params)) {
				$authParams->setConsumerParams($params);
			}
		}
		
		$authParams->setConsumerParam(self::FIELD_CALLBACK_URL, $callbackUrl);
	
		return $authParams;
	
	}
	
	
	/**
	 * Create instance of the RublonAuthParams configured for 2-factor login.
	 *
	 * @param RublonConsumer $rublon
	 * @param string $callbackUrl Callback URL address.
	 * @param string $userId
	 * @param string $userEmail
	 * @param RublonAuthParams|array $params Instance of the RublonAuthParams or consumer parameters array (optional)
	 * @return RublonAuthParams
	 */
	static public function initAuthParamsLogin(RublonConsumer $rublon, $callbackUrl, $userId, $userEmail, $params = null) {
		$authParams = self::initAuthParams($rublon, $callbackUrl, $params);
		$authParams->setConsumerParam(RublonAuthParams::FIELD_USER_ID, $userId);
		$authParams->setConsumerParam(RublonAuthParams::FIELD_USER_EMAIL_HASH, hash(self::HASH_ALG, strtolower($userEmail)));
		return $authParams;
	}
	
	
}
