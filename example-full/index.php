<?php

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
header( 'Content-type: text/html; charset=UTF-8' );

if (file_exists(dirname(__FILE__) . '/../env.php')) {
	require_once dirname(__FILE__) . '/../env.php';
}
if (!defined('ENVIRONMENT')) {
	define('ENVIRONMENT', 'sensorium');
}

require_once 'config.php';
require_once 'functions.php';
require_once 'hooks.php';

session_name('sdk');
session_start();

hook_bootstrap();

// CONTROLLER
if (!is_user_signed_in()) {
	if (!empty($_POST['login']) AND !empty($_POST['pass']) AND $user = authenticate($_POST['login'], $_POST['pass'])) {
		// Authentication logic
		if (login_user($user)) {
			// Successful login
			hook_auth_success($user);
			redirect($config['websiteUrl'] . '?rublonLogin=success');
		} else {
			// invalid login credentials
			logout();
		}
	} else {
		
		// Login page
		show_messages();
		echo '<html><head><title>Rublon Example</title><link rel="stylesheet" href="./style.css" type="text/css" /></head><body>
			<form method=post><input name=login><input type=password name=pass><input type=submit>';
		
		$gui = new Rublon2FactorGUI(
			$rublon,
			$userId = null,
			$userEmail = null,
			$logoutListener = false
		);
		echo $gui->getConsumerScript();
		
		// Rublon Subscribe Widget
		require_once '../libs/Rublon/core/HTML/RublonSubscribeWidget.php';
		echo '<div>' . new RublonSubscribeWidget() . '</div>';
		
		// Rublon Login Box
		require_once '../libs/Rublon/core/HTML/RublonLoginBox.php';
		echo '<div>' . new RublonLoginBox('./native-login.php') . '</div>';
		
		echo '</body></html>';
		
	}
} else { // User is logged in
	if (!empty($_GET['action']) AND $_GET['action'] == 'logout') {
		// Logout action
		logout();
		redirect('index.php');
		exit;
	}
	else if (!empty($_GET['action']) AND $_GET['action'] == 'confirm') {
		// Rublon transaction confirmation
		include 'confirm.php';
	}
	else if (!empty($_GET['action']) AND $_GET['action'] == 'features') {
		try {
			$client = new RublonAPIGetAvailableFeatures($rublon);
			$client->perform();
			echo '<pre>';
			print_r($client->getFeatures());
		} catch (Exception $e) {
			echo $e->getClient()->getRawRequestHeader();
			echo $e->getClient()->getRawRequestBody();
			echo $e->getClient()->getRawResponseHeader();
			echo $e->getClient()->getRawResponseBody();
			echo get_class($e);
			echo $e->getMessage();
		}
		exit;
	}
	else if (!empty($_GET['action']) AND $_GET['action'] == 'notification' AND !empty($_POST['url'])) {
		try {
			$client = new RublonAPINotification($rublon);
			$client->initUrlNotification('Demo notification', $_POST['url']);
			$client->perform();
			redirect('index.php?notification=sent');
		} catch (Exception $e) {
			var_dump($e);
			exit;
		}
	} else {
		// Load main page view
		include 'view.php';
	}
}
