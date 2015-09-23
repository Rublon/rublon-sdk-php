<?php

require_once 'RublonWidget.php';

class RublonSubscribeWidget extends RublonWidget {
	

	/**
	 * Device Widget HTML iframe attributes.
	 *
	 * @return array
	 */
	protected function getWidgetAttributes() {
		return array(
			'class' => 'rublon-subscribe-widget',
		);
	}
	
}
