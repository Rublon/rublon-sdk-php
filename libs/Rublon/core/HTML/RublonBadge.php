<?php

require_once 'RublonWidget.php';

class RublonBadge extends RublonWidget {
	

	

	/**
	 * Create Rublon Login Box
	 * 
	 */
	function __construct() {}
	

	/**
	 * Widget's HTML iframe attributes.
	 *
	 * @return array
	 */
	protected function getWidgetAttributes() {
		return array(
			'id' => 'RublonBadgeWidget'			
		);
	}
	
}
