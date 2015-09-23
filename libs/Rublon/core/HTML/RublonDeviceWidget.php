<?php

require_once 'RublonWidget.php';

class RublonDeviceWidget extends RublonWidget {
	

	/**
	 * Device Widget HTML iframe attributes.
	 *
	 * @return array
	 */
	protected function getWidgetAttributes() {
		return array(
			'id' => 'RublonDeviceWidget',
		);
	}
	
}
