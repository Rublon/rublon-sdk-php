<?php

require_once 'RublonAPIClient.php';

/**
 * API request: CheckUserDevice.
 *
 */
class RublonAPICheckUserDevice extends RublonAPIClient {
	
	const FIELD_PROFILE_ID = 'profileId';
	const FIELD_DEVICE_ID = 'deviceId';
	const FIELD_DEVICE_STATUS = 'deviceActive';
	
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
	protected $urlPath = '/api/v3/checkUserDevice';
	
	
	/**
	 * Constructor.
	 * 
	 * @param RublonConsumer $rublon Rublon instance.
	 * @param int $profileId Rublon user's ID.
	 * @param int $deviceId Device ID to check.
	 */
	public function __construct(RublonConsumer $rublon, $profileId, $deviceId) {
		
		parent::__construct($rublon);
		
		if (!$rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}
		
		// Set request URL and parameters
		$this->setRequestURL($rublon->getAPIDomain() . $this->urlPath);
		$this->setRequestParams(array(
			self::FIELD_PROFILE_ID => $profileId,
			self::FIELD_DEVICE_ID => $deviceId,
		));

	}
	
	
	public function perform() {
		$this->addRequestParams(array(
			self::FIELD_SYSTEM_TOKEN => $this->getRublon()->getSystemToken(),
		));
		return parent::perform();
	}
	
	
	/**
	 * Check if device is active.
	 * 
	 * @return boolean
	 */
	public function isDeviceActive() {
		if (isset($this->response[self::FIELD_RESULT][self::FIELD_DEVICE_STATUS])) {
			return !empty($this->response[self::FIELD_RESULT][self::FIELD_DEVICE_STATUS]);
		}
	}
	

}
