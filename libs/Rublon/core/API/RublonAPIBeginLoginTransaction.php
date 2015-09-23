<?php

require_once 'RublonAPIClient.php';

/**
 * API request: Credentials.
 *
 */
class RublonAPIBeginLoginTransaction extends RublonAPIClient {
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
	const URL_PATH = '/api/v3/beginLoginTransaction';
	
	
	const FIELD_SYSTEM_TOKEN = 'systemToken';
	const FIELD_CALLBACK_URL = 'callbackUrl';
	const FIELD_WEB_URI = 'webURI';

	
	/**
	 * Constructor.
	 *
	 * @param RublonConsumer $rublon
	 * @param string $callbackUrl Callback URL address.
	 * @param array $consumerParams Custom consumer parameters array (optional).
	 */
	public function __construct(RublonConsumer $rublon, $callbackUrl, array $consumerParams = array()) {
	
		parent::__construct($rublon);
		
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		
		$consumerParams[self::FIELD_SYSTEM_TOKEN] = $rublon->getSystemToken();
		$consumerParams[self::FIELD_CALLBACK_URL] = $callbackUrl;
		
		// Set request URL and parameters
		$url = $rublon->getAPIDomain() . self::URL_PATH;
		$this->setRequestURL($url)->setRequestParams($consumerParams);
	
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
