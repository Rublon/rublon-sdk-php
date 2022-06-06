<?php

namespace Rublon\Core\Api;

use Rublon\Core\RublonConsumer;
use Rublon\Core\Exceptions\RublonException;

/**
 * API request: Credentials.
 *
 */
class RublonAPICredentials extends RublonAPIClient {
	
	/**
	 * Invalid access token error.
	 */
	const ERROR_ACCESS_TOKEN = 'Invalid access token.';
	
	/**
	 * Field name with the additional confirmation result.
	 */
	const FIELD_CONFIRM_RESULT = 'answer';
	
	/**
	 * Field name for the Rublon profile ID.
	 */
	const FIELD_PROFILE_ID = 'profileId';
	
	/**
	 * Field name for the Rublon user's email.
	 */
	const FIELD_EMAIL = 'email';
	
	/**
	 * Field name for device ID.
	 */
	const FIELD_DEVICE_ID = 'deviceId';

	/**
	 * Field name for keeping info wether is project owner or not
	 * @var unknown
	 */
	const FIELD_PROJECT_OWNER = 'projectOwner';

    /**
     * Field name for type of credentials.
     */
    const FIELD_IS_PASSWORDLESS = 'isPasswordless';

    /**
     * Field name for the Rublon user's emails hash list.
     */
    const FIELD_EMAIL_HASH_LIST = 'emailHashList';
	
	/**
	 * User pressed the "Yes" button on the additional confirmation of the transaction.
	 */
	const CONFIRM_RESULT_YES = 'true';
	
	/**
	 * User pressed the "No" button on the additional confirmation of the transaction.
	 */
	const CONFIRM_RESULT_NO = 'false';
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
    protected $urlPath = '/api/transaction/credentials';


    /**
     * Constructor.
     *
     * @param RublonConsumer $rublon
     * @param string $accessToken
     * @throws RublonException
     */
	public function __construct(RublonConsumer $rublon, $accessToken, $isPasswordless = false) {
		
		parent::__construct($rublon);
		
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		if (!preg_match('/[a-z0-9]{60}/i', $accessToken)) {
			throw new RublonException(self::ERROR_ACCESS_TOKEN, RublonException::CODE_INVALID_ACCESS_TOKEN);
		}
		
		// Set request URL and parameters
		$url = $rublon->getAPIDomain() . $this->urlPath;
		$params = array(
			self::FIELD_SYSTEM_TOKEN => $rublon->getSystemToken(),
			self::FIELD_ACCESS_TOKEN => $accessToken,
			self::FIELD_IS_PASSWORDLESS => $isPasswordless,

		);
		$this->setRequestURL($url)->setRequestParams($params);

	}
	
	/**
	 * Get local username
	 *
	 * @return string
	 */
	public function getUsername()
    {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_USERNAME])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_USERNAME];
		}
	}
	
	
	/**
	 * Get the additional confirmation of the transaction result.
	 * 
	 * Method returns a result only if the Rublon::confirm()
	 * method was used to initialize the auth transaction.
	 *
	 * @return string
	 * @see Rublon::confirm()
	 * @see RublonAPICredentials::CONFIRM_RESULT_YES
	 * @see RublonAPICredentials::CONFIRM_RESULT_NO
	 */
	public function getConfirmResult() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_CONFIRM_RESULT])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_CONFIRM_RESULT];
		}
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


	public function getDeviceId() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_DEVICE_ID])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_DEVICE_ID];
		}
	}

    public function getUserEmailHashList() {
        if (isset($this->response[self::FIELD_RESULT][self::FIELD_EMAIL_HASH_LIST])) {
            return $this->response[self::FIELD_RESULT][self::FIELD_EMAIL_HASH_LIST];
        }
    }


}
