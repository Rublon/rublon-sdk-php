<?php

// Config
// System Token and Secret Key should be generated after addition of a project on https://developers.rublon.com

//----------------------------------
define('RUBLON_SYSTEM_TOKEN', 'PODAJ_SYSTEM_TOKEN');
define('RUBLON_SECRET_KEY', 'PODAJ_SECRET_KEY');
define('USER_PASSWORD', 'rublon123');

// Require libs
//----------------------------------
require_once '../libs/Rublon/Rublon2Factor.php';



//==========================================================================================
// Logic implementation - without Rublon code.
//==========================================================================================

header( 'Content-type: text/html; charset=UTF-8' );
session_name('rublon-sdk-example');
session_start();

hook_bootstrap();

if /* user is logged-in */ (!empty($_SESSION['user'])):

	if /* user wants to logout */ (!empty($_GET['action']) AND $_GET['action'] == 'logout'):
		unset($_SESSION['user']);
		header('Location: ./');
		exit;
	
	else /* show restricted page */: ?>

		<h1>Welcome <?php echo $_SESSION['user']; ?>!</h1>
		<?php hook_welcome_page(); ?>
		<p><a href="./?action=logout">Logout</a></p>
	
	<?php
	endif;

elseif /* user is trying to login */ (!empty($_POST['email']) AND !empty($_POST['password'])
		AND /* don't do this in your project, it's an example: */ $_POST['password'] == USER_PASSWORD):

	// Login the user:
	// (instead using the user's ID, we will authenticate him by his email address)
	hook_before_login($_POST['email']);
	$_SESSION['user'] = $_POST['email'];
	header('Location: ./');
	exit;

else /* show login page */: ?>
	
	<h1>Please login</h1>
	<form method="post">
		<p>Email: <input type="email" name="email" /></p>
		<p>Password: <input type="password" name="password" /></p>
		<p><input type="submit" value="Login" /></p>
	</form>
	
<?php
endif;


//==========================================================================================
// Hooks implementation - using Rublon SDK library.
//==========================================================================================


/**
 * Hook after the session has been initialized, but before processing the request.
 */
function hook_bootstrap() {
	if /* requesting Rublon callback */ (!empty($_GET['rublon']) AND $_GET['rublon'] == 'callback') {
		try {
			$rublon = new Rublon2Factor(RUBLON_SYSTEM_TOKEN, RUBLON_SECRET_KEY);
			$callback = new Rublon2FactorCallback($rublon);
			$callback->call(
				$successHandler = function($userId, Rublon2FactorCallback $callback) {
					// We are using the user's email instead a numeric ID:
					$_SESSION['user'] = $userId;
				},
				$cancelHandler = function(Rublon2FactorCallback $callback) {
					die('Request canceled');
				}
			);
			
			// If all ok, redirect to the main page:
			header('Location: ./');
			exit;

		} catch (RublonException $e) {
			die($e->getMessage());
		}
	}
}


/**
 * Hook before actual login the user, after the authentication was successful.
 *  
 * @param string $userEmail
 * @return void
 */
function hook_before_login($userEmail) {

	// Make sure that the user is not logged-in:
	unset($_SESSION['user']);
	
	try /* initialize the Rublon authentication */ {

		$rublon = new Rublon2Factor(RUBLON_SYSTEM_TOKEN, RUBLON_SECRET_KEY);
		$url = $rublon->auth(
			$callbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?rublon=callback',
			$userId = $userEmail,
			$userEmail,
			$extraParams = array()
		);
		if (!empty($url)) {
			// Redirect the user's browser to the Rublon's server to authenticate by Rublon:
			header('Location: '. $url);
			exit;
		} else {
			return; // User is not protected by Rublon, so bypass the second factor.
		}
	} catch (RublonException $e) {
		die($e->getMessage());
	}
}


/**
 * Display the Rublon GUI widgets.
 */
function hook_welcome_page() {
	echo '<style type="text/css">iframe {width: 500px; height: 400px; display: inline-block !important; border: 1px solid #cccccc;}</style>';
	$rublon = new Rublon2Factor(RUBLON_SYSTEM_TOKEN, RUBLON_SECRET_KEY);
	echo new Rublon2FactorGUI($rublon, $userId = $_SESSION['user'], $userEmail = $_SESSION['user']);
}
