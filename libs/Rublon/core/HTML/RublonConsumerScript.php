<?php

/**
 * Class for generating script tag that embeds consumer's JavaScript library.
 * 
 * The so-called "consumer script" is an individualized JavaScript library
 * that allows the website to use Rublon JavaScript elements - usually
 * the Rublon buttons. The library searches Rublon button HTML containers
 * in the website's DOM tree and fills them with proper buttons.
 * 
 * @see RublonButton
 */
class RublonConsumerScript {

	/**
	 * Template for script tag.
	 */
	const TEMPLATE_SCRIPT = '<script type="text/javascript" src="%s?t=%s"></script>';
	
	/**
	 * Consumer script URL.
	 */
	const URL_CONSUMER_SCRIPT = '/native/consumer_script_2factor';
		
	/**
	 * Rublon instance.
	 *
	 * @var RublonConsumer
	 */
	protected $rublon = null;
	
	/**
	 * Current user's ID.
	 * 
	 * @var string
	 */
	protected $userId;

	/**
	 * Current user's email address.
	 *
	 * @var string
	 */
	protected $userEmail;
	
	/**
	 * Wheter to listen on user's logout.
	 * 
	 * @var boolean
	 */
	protected $logoutListener = false;
	
	
	/**
	 * Initialize object with Rublon instance.
	 *
	 * A Rublon class instance is required for
	 * the object to work.
	 * 
	 * @param RublonConsumer $rublon An instance of the Rublon class
	 * @param string $userId User's ID in the local system.
	 * @param string $userEmail User's email address.
	 * @param boolean $logoutListener Wheter to listen on user's logout.
	 */
	public function __construct(RublonConsumer $rublon, $userId = null, $userEmail = null, $logoutListener = false) {
		$rublon->log(__METHOD__);
		$this->rublon = $rublon;
		$this->userId = $userId;
		$this->userEmail = $userEmail;
		$this->logoutListener = $logoutListener;
	}
	
	
	/**
	 * Set whether to listen on user's logout.
	 * 
	 * @param boolean $listen
	 * @return RublonConsumerScript
	 */
	public function setLogoutListener($listen) {
		$this->logoutListener = $listen;
		return $this;
	}
	
	/**
	 * Generate a HTML code of this object.
	 * 
	 * Returns a HTML script tag that will load the consumer
	 * script from the Rublon servers.
	 * 
	 * @return string
	 */
	public function __toString() {
		$this->getRublon()->log(__METHOD__);
		return sprintf(self::TEMPLATE_SCRIPT,
			$this->getConsumerScriptURL(),
			md5(microtime())
		);
	}
	
	
	/**
	 * Get consumer's script URL.
	 * 
	 * Returns the URL address of the consumer script on
	 * the Rublon servers.
	 *
	 * @return string
	 */
	protected function getConsumerScriptURL() {
		$this->getRublon()->log(__METHOD__);
		return $this->getRublon()->getAPIDomain()
			. self::URL_CONSUMER_SCRIPT . '/'
			. urlencode(base64_encode($this->getParamsWrapper())) . '/'
			. rand(1, 99999);
	}
	
	
	
	/**
	 * Get script input parameters.
	 * 
	 * @return array
	 */
	protected function getParams() {
		$params = array(
			RublonAuthParams::FIELD_ORIGIN_URL		=> $this->getRublon()->getCurrentUrl(),
			RublonAuthParams::FIELD_SYSTEM_TOKEN	=> $this->getRublon()->getSystemToken(),
			RublonAuthParams::FIELD_VERSION 		=> str_replace('-', '', $this->getRublon()->getVersionDate()),
			RublonAuthParams::FIELD_SERVICE 		=> $this->getRublon()->getServiceName(),
		);
		
		if (!empty($this->userEmail)) {
			$params[RublonAuthParams::FIELD_USER_EMAIL_HASH] = hash(RublonAuthParams::HASH_ALG, strtolower($this->userEmail));
		}
		if (!empty($this->userId)) {
			$params[RublonAuthParams::FIELD_USER_ID] = $this->userId;
		}
		if ($this->logoutListener) {
			$params[RublonAuthParams::FIELD_LOGOUT_LISTENER] = $this->logoutListener;
		}
		if ($lang = $this->getRublon()->getLang()) {
			$params[RublonAuthParams::FIELD_LANG] = $lang;
		}
		
		return $params;
		
	}
	
	
	/**
	 * Get signed script input parameters.
	 * 
	 * @return string
	 */
	protected function getParamsWrapper() {
		if ($this->getRublon()->isConfigured()) {
			$wrapper = new RublonSignatureWrapper;
			$wrapper->setSecretKey($this->getRublon()->getSecretKey());
			$wrapper->setBody($this->getParams());
			return (string)$wrapper;
		} else {
			return json_encode($this->getParams());
		}
	}
	

	/**
	 * Get Rublon instance.
	 *
	 * @return RublonConsumer
	 */
	public function getRublon() {
		return $this->rublon;
	}
	
	
}
