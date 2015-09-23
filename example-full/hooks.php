<?php
/**
 * Framework hooks implementation.
 * 
 * Use this function to implement your own integration.
 * 
 */


/**
 * After successful login.
 *
 * @return void
 */
function hook_auth_success() {
	global $config, $user, $rublon;

	try {

		// Create the Rublon auth URL:
		$url = $rublon->auth(
			$config['rublon']['callbackURL'],
			$user['login'],
			$user['email'],
			$params = array( // optional parameters
				RublonAuthParams::FIELD_LANG => $config['lang'],
				RublonAuthParams::FIELD_CUSTOM_URI_PARAM => 'login',
			)
		);

		if (!empty($url)) {
			// Rublon protection is active, so logout current user
			// to authenticate him in another step: Rublon.
			logout();
			// Redirect user to the Rublon auth process:
			redirect($url);
		}

	} catch (Exception $e) {
		// Remember to utilize your own error handler.
		logout();
		echo '['. get_class($e) . '] '. $e->getMessage();
		echo '<pre>' . ($e->getClient()->getRawRequest() . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . $e->getClient()->getRawResponse());
		exit;
	}

}


/**
 * Before the framework run.
 * 
 * @return void
 */
function hook_bootstrap() {
	global $rublon, $config;

	// Create the Rublon object:
	require_once './extended/MyRublon.php';
	require_once './extended/MyCallback.php';
	$rublon = new MyRublon($config['rublon']['systemToken'], $config['rublon']['secretKey']);

	if (!empty($_GET['rublon']) AND $_GET['rublon'] == 'callback') { // Rublon Callback URL
		try {
			$confirmResult = null;
			// Create instance of MyCallback which is the extended Rublon2FactorCallback class.
			$callback = new MyCallback($rublon);
			$callback->call(
				// The callback login function is given as an argument.
				function($userId, Rublon2FactorCallback $callback)
					use (&$confirmResult) { // <--- needed if this is a transaction confirmation.
					login_user($userId);
					$confirmResult = $callback->getCredentials()->getConfirmResult();
					// Save deviceId for remote logout:
					$response = $callback->getCredentials()->getResponse();
// 					var_dump($response);exit;
					if (isset($response['result']['deviceId'])) {
						$_SESSION['rublonDeviceId'] = $response['result']['deviceId'];
					}
				},
				// The cancel handler function:
				function(Rublon2FactorCallback $callback) {
					if (!empty($_GET['custom']) AND $_GET['custom'] == 'confirm') die('canceled');
					else redirect('./?rublon=cancel');
				}
			);
			if (!is_null($confirmResult)) {
				transaction_confirm_result($confirmResult == RublonAPICredentials::CONFIRM_RESULT_YES, $withRublon = true);
				exit;
			} else {
				redirect($config['websiteUrl'] . '?rublonLogin=success');
			}
		} catch (Exception $e) {
			// Remember to utilize your own error handler.
			if (!empty($_GET['error']) AND $_GET['error'] == 'timeout') {
				die('timeout error');
			}
			var_dump(get_class($e));
			echo $e->getMessage();
			var_dump($e->getPrevious());
			exit;
		}
	}
}
