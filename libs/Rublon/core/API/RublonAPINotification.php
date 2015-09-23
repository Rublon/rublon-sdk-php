<?php

require_once 'RublonAPIClient.php';

/**
 * API request: Credentials.
 *
 */
class RublonAPINotification extends RublonAPIClient {
	
	const FIELD_NOTIFICATION_CHANNEL = 'channel';
	const FIELD_NOTIFICATION_TITLE = 'title';
	const FIELD_NOTIFICATION_URL = 'url';
	const FIELD_NOTIFICATION_TYPE = 'type';
	
	const TYPE_URL = 'URL';
	
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
	protected $urlPath = '/api/v3/notification';

	
	protected $notificationChannel = null;
	
	protected $notificationTitle = null;
	
	protected $notificationUrl = null;
	
	
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
	
	
	public function setNotificationChannel($channel) {
		$this->notificationChannel = $channel;
		return $this;
	}
	
	
	public function initUrlNotification($title, $url) {
		$this->setNotificationType(self::TYPE_URL);
		$this->setNotificationTitle($title);
		$this->setNotificationUrl($url);
	}
	
	
	public function setNotificationTitle($title) {
		$this->notificationTitle = $title;
		return $this;
	}
	
	public function setNotificationUrl($url) {
		$this->notificationUrl = $url;
		return $this;
	}
	
	public function setNotificationType($type) {
		$this->notificationType = $type;
		return $this;
	}
	
	
	public function perform() {
		$this->addRequestParams(array(
			self::FIELD_SYSTEM_TOKEN => $this->getRublon()->getSystemToken(),
			self::FIELD_NOTIFICATION_CHANNEL => $this->notificationChannel,
			self::FIELD_NOTIFICATION_TITLE => $this->notificationTitle,
			self::FIELD_NOTIFICATION_URL => $this->notificationUrl,
			self::FIELD_NOTIFICATION_TYPE => $this->notificationType,
		));
		return parent::perform();
	}
	

}
