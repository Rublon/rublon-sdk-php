<?php
/**
 * Example of the framework build-in functions.
 * 
 * You don't need to dive here.
 * 
 */


/**
 * Authenticate user by given login and password.
 * 
 * @param string $login
 * @param string $pass
 * @return array|false Returns user array or false if not authenticated.
 */
function authenticate($login, $pass) {
	$users = json_decode(file_get_contents('users.json'), true);
	if (isset($users[$login]) AND $users[$login]['pass'] == $pass) {
		return $users[$login];
	} else {
		return false;
	}
}

/**
 * Check whether some user is logged in.
 * 
 * @return boolean
 */
function is_user_signed_in() {
	return (!empty($_SESSION['user']));
}


/**
 * Login user by given ID or user array.
 * 
 * @param array|string $user User's array or ID.
 * @return boolean Return true if user has been successful logged in.
 */
function login_user($user) {
	if (is_string($user)) { // get user by login
		$users = json_decode(file_get_contents('users.json'), true);
		if (isset($users[$user])) {
			$user = $users[$user];
		}
	}
	if (is_array($user) AND !empty($user)) { // save user in session
		$_SESSION['user'] = $user;
		return true;
	} else {
		return false;
	}
}


/**
 * Confirm the transaction.
 * 
 * @param bool $confirmResult Whether the user has confirmed the transaction.
 * @param bool $withRublon Transaction has been confirmed with Rublon.
 * @return void
 */
function transaction_confirm_result($confirmResult, $withRublon) {
	echo '<script>
		parent.document.getElementById("confirmForm").RublonConfirmationCallback('. json_encode(sprintf('[%s] Transaction result: %s',
			$withRublon ? 'Rublon was used' : 'Without Rublon',
			$confirmResult ? 'true' : 'false'
		)) .');
	</script>';
}


/**
 * Redirect browser to given URL.
 * 
 * @param string $url
 * @return void
 */
function redirect($url) {
	header('Location: '. $url);
	exit;
}


/**
 * Logout user.
 * 
 * @return void
 */
function logout() {
	session_destroy();
}


/**
 * Show messages block.
 *
 * @return void
 */
function show_messages() {
	if (!empty($_SESSION['msg'])) {
		foreach ($_SESSION['msg'] as $msg) {
			echo '<div style="padding:1em;margin:1em 0;background:#f0f0f0;border:1px solid #cccccc;">'. $msg .'</div>';
		}
		$_SESSION['msg'] = array();
	}
}


ini_set('session.save_path', dirname(__FILE__) . '/tmp');
session_set_save_handler(
	function($savePath, $sessionName) { // open
		if (!is_dir($savePath)) {
			mkdir('./tmp', 0777);
		}
		return true;
	},
	function() { // close
		return true;
	},
	function($sessionId) { // read
		return (string)@file_get_contents(ini_get('session.save_path') . "/sess_$sessionId");
	},
	function($sessionId, $data) { // write
		$file = ini_get('session.save_path') . "/sess_$sessionId";
		$result = file_put_contents($file, $data) === false ? false : true;
		if ($result) chmod($file, 0666);
		return $result;
	},
	function($sessionId) { // destroy
		$file = ini_get('session.save_path') . "/sess_$sessionId";
		if (file_exists($file)) {
			unlink($file);
		}
		return true;
	},
	function($lifetime) { //gc
		foreach (glob(ini_get('session.save_path') . "/sess_*") as $file) {
			if (filemtime($file) + $lifetime < time() && file_exists($file)) {
				unlink($file);
			}
		}
		return true;
	}
);
