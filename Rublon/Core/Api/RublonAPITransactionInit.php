<?php

namespace Rublon\Core\Api;

use Rublon\Core\Exceptions\Client\MissingField_RublonClientException;
use Rublon\Core\RublonConsumer;

/**
 * API request: Init Transaction.
 *
 */
class RublonAPITransactionInit extends RublonAPIClient {
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
    const URL_PATH = '/api/transaction/init';
	
	
	const FIELD_APP_USER_ID = 'appUserId';
	const FIELD_USER_EMAIL_HASH = 'userEmailHash';
	const FIELD_USER_EMAIL = 'userEmail';
	const FIELD_SYSTEM_TOKEN = 'systemToken';
	const FIELD_CALLBACK_URL = 'callbackUrl';
	const FIELD_WEB_URI = 'webURI';
    const FIELD_CONSUMER_PARAMS = 'consumerParams';
    const FIELD_PASSWORDLESS = 'isPasswordless';
	
	/**
	 * Constructor.
	 *
	 * @param RublonConsumer $rublon
	 * @param string $callbackUrl Callback URL address.
	 * @param string $appUserId User's ID in local system.
	 * @param string $userEmail User's email address.
	 * @param array $consumerParams Custom consumer parameters array (optional).
     * @param boolean $isPasswordless param for passwordless authentication
	 */
	public function __construct(RublonConsumer $rublon, $callbackUrl, $userEmail, $appUserId, array $consumerParams = array(), $isPasswordless = false) {
	
		parent::__construct($rublon);
		
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		
		$params[self::FIELD_SYSTEM_TOKEN] = $rublon->getSystemToken();
		$params[self::FIELD_APP_USER_ID] = $appUserId;
        if (!empty($userEmail)) {
            $params[self::FIELD_USER_EMAIL_HASH] = hash(self::HASH_ALG, strtolower($userEmail));
            $params[self::FIELD_USER_EMAIL]      = strtolower($userEmail);
        }
		$params[self::FIELD_CALLBACK_URL] = $callbackUrl;
        $params[self::FIELD_PASSWORDLESS] = $isPasswordless;
        if (!empty($consumerParams)) {
            $params[self::FIELD_CONSUMER_PARAMS] = $consumerParams;
        }
		
		// Set request URL and parameters
		$url = $rublon->getAPIDomain() . self::URL_PATH;
		$this->setRequestURL($url)->setRequestParams($params);
	
	}
	
	
	/**
	 * Returns URI to redirect to.
	 * 
	 * @return string
	 */
	public function getWebURI() {
		return $this->response[self::FIELD_RESULT][self::FIELD_WEB_URI];
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see RublonAPIClient::validateResponse()
	 */
	protected function validateResponse() {
		if (parent::validateResponse()) {
			if (!empty($this->response[self::FIELD_RESULT][self::FIELD_WEB_URI])) {
				return true;
			} else throw new MissingField_RublonClientException($this, self::FIELD_WEB_URI);
		}
	}
	

}
