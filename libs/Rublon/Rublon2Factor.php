<?php

require_once 'core/RublonConsumer.php';
require_once 'Rublon2FactorCallback.php';
require_once 'Rublon2FactorGUI.php';
require_once 'core/API/RublonAPICredentials.php';
require_once 'core/API/RublonAPIBeginTransaction.php';
require_once 'core/API/RublonAPINotification.php';
require_once 'core/API/RublonAPIGetAvailableFeatures.php';

/**
 * Class provides methods used by `Rublon Two Factor` service process.
 *
 */
class Rublon2Factor extends RublonConsumer {
	
	/**
	 * Service name.
	 * 
	 * @var string
	 */
	protected $serviceName = '2factor';

	/**
	 * Cached credentials.
	 *
	 * @var array
	 */
	protected $cacheCredentials = array();
	
	
	
	/**
	 * Initializes the Rublon authentication transaction
	 * and returns the URL address to redirect user's browser
	 * or NULL if user's account is not protected.
	 * 
	 * First, method checks the account's protection status in the Rublon server for current user.
	 * If user has protected this account, method returns the URL address.
	 * Redirect user's browser to this URL to start the Rublon authentication process.
	 * 
	 * If Rublon user has deleted his Rublon account or Rublon API is not available at this time,
	 * method returns false. If so, just bypass Rublon and sign in the user.
	 * 
	 * Notice: to use this method the configurations values (system token and secret key)
	 * must be provided to the constructor. If not, function will trigger an E_USER_ERROR.
	 * 
	 * @param string $callbackUrl Callback URL address.
	 * @param string $userId User's ID in local system.
	 * @param string $userEmail User's email address.
	 * @param array $consumerParams Custom consumer parameters array (optional).
	 * @return Ambigous <string, NULL> URL to redirect or NULL if user is not protected.
	 * @throws RublonException
	 */
	public function auth($callbackUrl, $userId, $userEmail, array $consumerParams = array()) {
		
		$this->log(__METHOD__);
		
		if (!$this->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
			return null;
		}
		
		if ($lang = $this->getLang()) {
			$consumerParams[RublonAuthParams::FIELD_LANG] = $lang;
		}
		
		try {
			$beginTransaction = new RublonAPIBeginTransaction($this, $callbackUrl, $userEmail, $userId, $consumerParams);
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
	 * Authenticate user and perform an additional confirmation of the transaction.
	 * 
	 * This method requires user to use the Rublon mobile app
	 * (even if the Trusted Device is available)
	 * and confirm transaction to maintain higher security level.
	 * The message passed in the $customMessage argument will be displayed
	 * in the confirmation dialog on the user's mobile.
	 * 
	 * @param string $callbackUrl
	 * @param string $userId
	 * @param string $userEmail
	 * @param string $confirmMessage
	 * @param array $consumerParams
	 * @return Ambigous <string, NULL> URL to redirect or NULL if user is not protected.
	 * @throws RublonException
	 * @see RublonAPICredentials::getConfirmResult()
	 */
	public function confirm($callbackUrl, $userId, $userEmail, $confirmMessage, array $consumerParams = array()) {
		$consumerParams[RublonAuthParams::FIELD_CONFIRM_MESSAGE] = $confirmMessage;
		if ($lang = $this->getLang()) {
			$consumerParams[RublonAuthParams::FIELD_LANG] = $lang;
		}
		return $this->auth($callbackUrl, $userId, $userEmail, $consumerParams);
	}
	
	
	/**
	 * Perform a confirmation of the transaction without user's action needed
	 * if the time buffer after previous confirmation has not been reached.
	 *
	 * If the amount of seconds after the previous transaction is less than
	 * given time buffer, Rublon will confirm the transaction without user's action.
	 * In other cases, this method will behave the same as the Rublon2Factor::confirm() method.
	 *
	 * @param string $callbackUrl
	 * @param string $userId
	 * @param string $userEmail
	 * @param string $confirmMessage
	 * @param int $timeBuffer
	 * @param array $consumerParams
	 * @return Ambigous <string, NULL> URL to redirect or NULL if user is not protected.
	 * @throws RublonException
	 * @see Rublon2Factor::confirm()
	 * @see RublonAPICredentials::getConfirmResult()
	 */
	public function confirmWithBuffer($callbackUrl, $userId, $userEmail, $confirmMessage, $timeBuffer, array $consumerParams = array()) {
		$consumerParams[RublonAuthParams::FIELD_CONFIRM_TIME_BUFFER] = $timeBuffer;
		return $this->confirm($callbackUrl, $userId, $userEmail, $confirmMessage, $consumerParams);
	}
	
	
	/**
	 * Authenticate user and get user's credentials using one-time use access token and expected user's profile ID.
	 *
	 * One-time use access token is a session identifier which will be deleted after first usage.
	 * This method can be called only once in authentication process.
	 *
	 * @param string $accessToken One-time use access token
	 * @return RublonAPICredentials
	 * @throws RublonException
	 */
	public function getCredentials($accessToken) {
		if (isset($this->cacheCredentials[$accessToken])) {
			$this->log('return cached credentials');
			return $this->cacheCredentials[$accessToken];
		} else {
			$credentials = new RublonAPICredentials($this, $accessToken);
			$credentials->perform();
			$this->cacheCredentials[$accessToken] = $credentials;
			return $credentials;
		}
	}
		
	
}
