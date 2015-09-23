<?php
/**
 * Transaction confirmation logic.
 */

$user = $_SESSION['user'];
$transactionMessage = (!empty($_POST['confirmMessage']) ? $_POST['confirmMessage'] : 'Please confirm transaction: '. rand(1000, 9999));

// Create the Rublon auth URL
if ($timeBuffer = filter_input(INPUT_POST, 'buffer', FILTER_SANITIZE_NUMBER_INT)) {
	// Confirmation with time buffer
	$url = $rublon->confirmWithBuffer(
		$config['rublon']['callbackURL'],
		$user['login'],
		$user['email'],
		$transactionMessage,
		$timeBuffer,
		$params = array( // optional parameters
			RublonAuthParams::FIELD_CUSTOM_URI_PARAM => 'confirm',
		));
} else {
	// Confirmation without time buffer
	$url = $rublon->confirm(
		$config['rublon']['callbackURL'],
		$user['login'],
		$user['email'],
		$transactionMessage,
		$params = array( // optional parameters
			RublonAuthParams::FIELD_CUSTOM_URI_PARAM => 'confirm',
	));
}

if (!empty($url)) { // Redirect to the Rublon confirmation process:
	redirect($url);

} else { // Simply confirm the transaction:
	transaction_confirm_result($confirmResult = true, $withRublon = false);
	exit;
}
