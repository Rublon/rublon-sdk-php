<?php

// Config
//----------------------------------
define('SYSTEM_TOKEN', '_PASTE_SYSTEM_TOKEN_HERE_');
define('SECRET_KEY', '_PASTE_SECRET_KEY_HERE_');

// List of local users (add here your email address registered in Rublon):
$users = array('user@example.com', 'user2@example.com', 'unknown@example.com');
// Create a helper hash-to-email map:
$usersEmailsHashList = array();
foreach ($users as $email) {
	$usersEmailsHashList[hash('sha256', $email)] = $email;
}




// Require libs
//----------------------------------
require_once '../libs/Rublon/RublonLogin.php';
require_once '../libs/Rublon/Rublon2Factor.php';


//==========================================================================================
// Logic implementation
//==========================================================================================

header( 'Content-type: text/html; charset=UTF-8' );
session_name('rublon-sdk-example-login');
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

elseif /* user is trying to login using Rublon */ (!empty($_GET['action']) AND $_GET['action'] == 'rublon'):

	try /* to begin the Rublon transaction */ {
		$callbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '?rublon=callback';
		$rublon = new RublonLogin(SYSTEM_TOKEN, SECRET_KEY);
		header('Location: ' . $rublon->auth($callbackUrl));
	} catch (Exception $e) {	  
		echo $e->getMessage();
	}
	exit;

else /* show login page */: ?>
	
	<h1>Hi Anonymous</h1>
	
	<?php
	
	$loginUrl = 'http://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '?action=rublon';
	$rublon = new RublonLogin(SYSTEM_TOKEN, SECRET_KEY);
	$gui = new RublonGUI($rublon);
	echo $gui->getConsumerScript();
	echo new RublonLoginBox($loginUrl);
	
	?>
	
<?php
endif;


//==========================================================================================
// Hooks implementation
//==========================================================================================


/**
 * Hook after the session has been initialized, but before processing the request.
 */
function hook_bootstrap() {
	
	global $usersEmailsHashList;
	
	if /* requesting Rublon callback */ (!empty($_GET['rublon']) AND $_GET['rublon'] == 'callback') {
		try {
			$rublon = new RublonLogin(SYSTEM_TOKEN, SECRET_KEY);
			$credentials = $rublon->getCredentials($_GET['token']);
			
			if ($userEmail = $credentials->getUserEmail()) {
				if (in_array($userEmail, $users)) {
					$_SESSION['user'] = $userEmail;
				}
			} else {
				$hashList = $credentials->getUserEmailHashList();
				foreach ($hashList as $record) {
					if (isset($usersEmailsHashList[$record['hash']])) {
						$_SESSION['user'] = $usersEmailsHashList[$record['hash']];
					}
				}
			}
			
			if (empty($_SESSION['user'])) {
				die('User not found');
			} else {
				header('Location: ./');
				exit;
			}

		} catch (RublonException $e) {
			echo '<pre>';
			echo $e->getClient()->getRawRequest() . PHP_EOL;
			echo $e->getClient()->getRawResponse() . PHP_EOL;
			echo get_class($e) . PHP_EOL;
			die($e->getMessage());
		}
	}
}


/**
 * Display the Rublon GUI widgets.
 */
function hook_welcome_page() {
	echo '<style type="text/css">iframe {width: 500px; height: 400px; display: inline-block !important; border: 1px solid #cccccc;}</style>';
	$rublon = new RublonLogin(SYSTEM_TOKEN, SECRET_KEY);
	$gui = new RublonGUI($rublon, $userId = $_SESSION['user'], $userEmail = $_SESSION['user']);
	echo $gui->getConsumerScript();
	echo new RublonDeviceWidget();
}
