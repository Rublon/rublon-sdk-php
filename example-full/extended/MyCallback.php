<?php

require_once dirname(__FILE__) . '/../../libs/Rublon/Rublon2FactorCallback.php';

class MyCallback extends Rublon2FactorCallback {
	
	
	/**
	 * Delete sessions with given userId and deviceId.
	 * 
	 * @see Rublon2FactorCallback::handleLogout()
	 */
	protected function handleLogout($userId, $deviceId) {
		foreach (glob(ini_get('session.save_path') . "/sess_*") as $file) {
			$contents = @file_get_contents($file);
			session_decode($contents);
			if (!empty($_SESSION['user']) AND !empty($_SESSION['user']['login']) AND $_SESSION['user']['login'] == $userId
					AND (empty($_SESSION['rublonDeviceId']) OR $_SESSION['rublonDeviceId'] == $deviceId)) {
				unlink($file);
			}
		}
	}
	
}