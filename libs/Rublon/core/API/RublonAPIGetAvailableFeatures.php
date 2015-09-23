<?php

require_once 'RublonAPIClient.php';

/**
 * API request: GetAvailableFeatures.
 *
 */
class RublonAPIGetAvailableFeatures extends RublonAPIClient {
	
	const FIELD_FEATURES = 'features';
	
	const FEATURE_FORCE_MOBILE_APP = 'forceMobileApp';
	const FEATURE_IGNORE_TRUSTED_DEVICE = 'ignoreTrustedDevice';
	const FEATURE_OPERATION_CONFIRMATION = 'operationConfirmation';
	const FEATURE_BUFFERED_CONFIRMATION = 'bufferedAutoConfirmation';
	const FEATURE_IDENTITY_PROVIDING = 'accessControlManager';
	const FEATURE_REMOTE_LOGOUT = 'remoteLogout';
	
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
	protected $urlPath = '/api/v3/getAvailableFeatures';
	
	
	/**
	 * Constructor.
	 * 
	 * @param RublonConsumer $rublon
	 */
	public function __construct(RublonConsumer $rublon) {
		
		parent::__construct($rublon);
		
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		
		// Set request URL and parameters
		$this->setRequestURL($rublon->getAPIDomain() . $this->urlPath);

	}
	
	
	public function perform() {
		$this->addRequestParams(array(
			self::FIELD_SYSTEM_TOKEN => $this->getRublon()->getSystemToken(),
		));
		return parent::perform();
	}
	
	
	/**
	 * Get features list from response.
	 * 
	 * @return array|NULL
	 */
	public function getFeatures() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_FEATURES])) {
			return $this->response[self::FIELD_RESULT][self::FIELD_FEATURES];
		}
	}
	

}
