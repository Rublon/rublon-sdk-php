<?php

require_once 'RublonAPIClient.php';

/**
 * API request: Credentials.
 *
 */
class RublonAPILoginCredentials extends RublonAPIClient {
	
	/**
	 * Field name for the Rublon profile ID.
	 */
	const FIELD_PROFILE_ID = 'profileId';
	
	/**
	 * Field name for the Rublon user's email.
	 */
	const FIELD_EMAIL_HASH_LIST = 'emailHashList';
	
	/**
	 * Field name for the Rublon user's email.
	 */
	const FIELD_EMAIL = 'email';
	
	/**
	 * Field name for device ID.
	 */
	const FIELD_DEVICE_ID = 'deviceId';
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
	protected $urlPath = '/api/v3/loginCredentials';

	
	/**
	 * Constructor.
	 * 
	 * @param RublonConsumer $rublon
	 * @param string $accessToken
	 */
	public function __construct(RublonConsumer $rublon, $accessToken) {
		
		parent::__construct($rublon);
		
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		
		// Set request URL and parameters
		$url = $rublon->getAPIDomain() . $this->urlPath;
		$params = array(
			self::FIELD_SYSTEM_TOKEN => $rublon->getSystemToken(),
			self::FIELD_ACCESS_TOKEN => $accessToken,
		);
		$this->setRequestURL($url)->setRequestParams($params);

	}
	
	
	/**
	 * Get Rublon user's ID.
	 *
	 * @return int
	 */
	public function getProfileId() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_PROFILE_ID])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_PROFILE_ID];
		}
	}
	
	
	/**
	 * Get Rublon user's email address.
	 *
	 * @return string
	 */
	public function getUserEmail() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_EMAIL])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_EMAIL];
		}
	}
	
	
	public function getUserEmailHashList() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_EMAIL_HASH_LIST])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_EMAIL_HASH_LIST];
		}
	}
	
	
	public function getDeviceId() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_DEVICE_ID])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_DEVICE_ID];
		}
	}


}
