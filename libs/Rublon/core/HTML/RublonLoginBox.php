<?php

require_once 'RublonWidget.php';

class RublonLoginBox extends RublonWidget {
	
	
	protected $loginUrl;
	protected $size;
	

	/**
	 * Create Rublon Login Box
	 * 
	 * @param string $loginUrl
	 */
	function __construct($loginUrl, $size = 'small') {
		$this->loginUrl = $loginUrl;
		$this->size = $size;
	}
	

	/**
	 * Widget's HTML iframe attributes.
	 *
	 * @return array
	 */
	protected function getWidgetAttributes() {
		return array(
			'id' => 'RublonLoginBoxWidget',
			'data-login-url' => $this->loginUrl,
		    'data-size' => $this->size
		);
	}
	
}
