<?php

require_once 'core/RublonConsumer.php';
require_once 'core/RublonGUI.php';
require_once 'core/API/RublonAPIBeginLoginTransaction.php';
require_once 'core/API/RublonAPILoginCredentials.php';

class RublonLogin extends RublonConsumer {

	/**
	 * Service name.
	 *
	 * @var string
	 */
	protected $serviceName = 'native';
	

	/**
	 * Get authentication URL.
	 *
	 * @param string $callbackUrl
	 * @param array $consumerParams Extra authentication parameters.
	 * @return string
	 */
	function auth($callbackUrl, array $consumerParams = array()) {
		
		$this->log(__METHOD__);
		
		if (!$this->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
			return null;
		}
		
		if ($lang = $this->getLang()) {
			$consumerParams[RublonAuthParams::FIELD_LANG] = $lang;
		}
		
		try {
			$beginTransaction = new RublonAPIBeginLoginTransaction($this, $callbackUrl, $consumerParams);
			$beginTransaction->perform();
			return $beginTransaction->getWebURI();
		} catch (UserNotFound_RublonAPIException $e) {
			// bypass Rublon
			return null;
		} catch (RublonException $e) {
			throw $e;
		}
	
	}
	
	
	/**
	 * Get credentials after authentication.
	 *
	 * @param string $accessToken
	 * @return RublonAPICredentials
	 */
	function getCredentials($accessToken) {
		$credentials = new RublonAPILoginCredentials($this, $accessToken);
		$credentials->perform();
		return $credentials;
	}
	
}
