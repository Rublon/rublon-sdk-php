<?php

require_once('HTML/RublonButton.php');
require_once('HTML/RublonConsumerScript.php');
require_once('HTML/RublonDeviceWidget.php');
require_once('HTML/RublonShareAccessWidget.php');
require_once('HTML/RublonLoginBox.php');


class RublonGUI {
	

	/**
	 * Rublon instance.
	 *
	 * @var RublonConsumer
	 */
	protected $rublon;
	
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
	 * Constructor.
	 *
	 * @param Rublon2Factor $rublon Rublon instance.
	 * @param string $userId Current user's ID.
	 * @param string $userEmail Current user's email.
	 * @param boolean $logoutListener Set whether to listen on user's logout.
	 */
	public function __construct(RublonConsumer $rublon, $userId = null, $userEmail = null, $logoutListener = false) {
		$this->rublon = $rublon;
		$this->userId = $userId;
		$this->userEmail = $userEmail;
		$this->logoutListener = $logoutListener;
	}
	

	/**
	 * Returns HTML code to embed consumer script.
	 *
	 * @return string
	 */
	public function getConsumerScript() {
		return (string)new RublonConsumerScript($this->getRublon(), $this->userId, $this->userEmail, $this->logoutListener);
	}
	

	/**
	 * Get Rublon instance.
	 *
	 * @return RublonConsumer
	 */
	protected function getRublon() {
		return $this->rublon;
	}
	
	
	
}
