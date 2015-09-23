<?php

if (!defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', '/');
}

require_once 'RublonException.php';
require_once 'RublonAuthParams.php';
require_once 'RublonSignatureWrapper.php';


/**
 * Abstract class for Rublon services.
 * 
 * Class defines common interface for given Rublon service instance.
 * 
 * @abstract
 */
abstract class RublonConsumer {

	/**
	 * Latest version release of this library.
	 */
	const VERSION = '3.7.1';
	
	/**
	 * Latest version release date of this library.
	 */
	const VERSION_DATE = '2015-07-02';
	
	/**
	 * Default API domain.
	 */
	const DEFAULT_API_DOMAIN = "https://code.rublon.com";
	
	/**
	 * Default technology code name.
	 */
	const DEFAULT_TECHNOLOGY = "rublon-php-sdk";
	
	/**
	 * Configuration error message template.
	 */
	const TEMPLATE_CONFIG_ERROR = 'Before calling Rublon authentication you have to pass the consumer\'s system token and secret key to the Rublon class constructor.';

	/**
	 * System token.
	 *
	 * @var string
	 */
	protected $systemToken;
	
	/**
	 * Secret key.
	 *
	 * @var string
	 */
	protected $secretKey;

	/**
	 * Service name.
	 *
	 * @var string
	 */
	protected $serviceName = '';
	
	/**
	 * API server name.
	 * 
	 * @var string
	 */
	protected $apiServer;
	
	
	
	/**
	 * Construct the object.
	 * 
	 * @param string $systemToken Consumer's system token string.
	 * @param string $secretKey Consumer's secret key string.
	 * @param string $apiServer API server's URI
	 */
	public function __construct($systemToken = null, $secretKey = null, $apiServer = null) {
		$this->log(__METHOD__);
		$this->systemToken = $systemToken;
		$this->secretKey = $secretKey;
		$this->apiServer = $apiServer;
	}
	

	/**
	 * Get secret key.
	 *
	 * @return string
	 */
	public function getSecretKey() {
		return $this->secretKey;
	}
	
	
	/**
	 * Get system token.
	 *
	 * @return string
	 */
	public function getSystemToken() {
		return $this->systemToken;
	}
	
	
	/**
	 * Get Rublon API domain.
	 *
	 * @return string
	 */
	public function getAPIDomain() {
		if (empty($this->apiServer)) {
			return self::DEFAULT_API_DOMAIN;
		} else {
			return $this->apiServer;
		}
	}
	
	
	/**
	 * Get language to use in Rublon GUI.
	 * If null then language will be forecast from the HTTP headers
	 * sent by user's browser to the Rublon server.
	 * 
	 * @return string
	 */
	public function getLang() {
		return null;
	}
	
	
	/**
	 * Perform a redirection and exit.
	 *
	 * @param string $url
	 * @return void
	 */
	public function redirect($url) {
		header('Location: ' . $url);
		exit;
	}
	
	
	/**
	 * Get the version date.
	 *
	 * @return string
	 */
	public function getVersionDate() {
		return self::VERSION_DATE;
	}
	
	/**
	 * Get the version.
	 *
	 * @return string
	 */
	public function getVersion() {
		return self::VERSION;
	}
	
	
	/**
	 * Get the module's technology.
	 *
	 * @return string
	 */
	public function getTechnology() {
		return self::DEFAULT_TECHNOLOGY;
	}
	
	
	/**
	 * Get the service name.
	 * 
	 * @return string
	 */
	public function getServiceName() {
		return $this->serviceName;
	}
	
	
	/**
	 * Check whether service is configured.
	 * 
	 * @return boolean
	 */
	public function isConfigured() {
		$systemToken = $this->getSystemToken();
		$secretKey = $this->getSecretKey();
		return (!empty($systemToken) AND !empty($secretKey));
	}
	

	/**
	 * Log a message.
	 *
	 * @param mixed $msg
	 * @return RublonConsumer
	 */
	public function log($msg) {
		return $this;
	}

	
	/**
	 * Check if current user has permission to activate/register Rublon module.
	 * Override if your module uses the consumer registration API.
	 * Otherwise return null.
	 *
	 * @return boolean
	 */
	public function canUserActivate() {
		return false;
	}
	
	
	/**
	 * Returns current URL.
	 * 
	 * @return string
	 */
	public function getCurrentUrl() {
		if (!empty($_SERVER['HTTP_HOST'])) {
			$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
			return ($ssl ? 'https' : 'http') .'://'. $_SERVER['HTTP_HOST'] . (empty($_SERVER['REQUEST_URI']) ? '/' : $_SERVER['REQUEST_URI']);
		}
	}

	
}
