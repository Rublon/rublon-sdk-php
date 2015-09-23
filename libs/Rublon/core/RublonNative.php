<?php

require_once 'RublonConsumer.php';

class RublonNative extends RublonConsumer {
	
	/**
	 * Service name.
	 *
	 * @var string
	 */
	protected $serviceName = 'native';
	
	/**
	 * URL path to redirect.
	 * 
	 * @var string
	 */
	protected $urlPath = '/code/native/';
	
	
	/**
	 * Get authentication URL.
	 *
	 * @param array $params Auth parameters.
	 * @return string
	 */
	function getAuthURL(array $params = array()) {
		
		$params[RublonAuthParams::FIELD_VERSION] = $this->getVersionDate();
		$authString = array();
		
		if (!empty($params)) {
			$authString['consumerParams'] = RublonSignatureWrapper::wrap($this->getSecretKey(), $params);
		}
		
		$authString[RublonAuthParams::FIELD_SYSTEM_TOKEN] = $this->getSystemToken();
		$authString[RublonAuthParams::FIELD_LANG] = $this->getLang();
		$authString[RublonAuthParams::FIELD_WINDOW_TYPE] = 'window';
		
		return $this->getAPIDomain() . $this->urlPath . urlencode(base64_encode(json_encode($authString)));
		
	}
	
	
	/**
	 * Get credentials after authentication.
	 * 
	 * @param string $accessToken
	 * @return RublonAPICredentials
	 */
	function getCredentials($accessToken) {
		$credentials = new RublonAPICredentials($this, $accessToken);
		$credentials->perform();
		return $credentials;
	}
	
}
