<?php

require_once('Rublon2Factor.php');
require_once('core/RublonGUI.php');


/**
 * Class to create Rublon GUI elements.
 * 
 * To display the Rublon GUI you can just echo the class instance.
 *
 */
class Rublon2FactorGUI extends RublonGUI {
	
	
	/**
	 * Template for user box container.
	 */
	const TEMPLATE_BOX_CONTAINER = '<div class="rublon-box" data-configured="%d" data-can-activate="%d">%s</div>';
	
	
	/**
	 * Create user box.
	 * 
	 * @return string
	 */
	public function userBox() {
		return $this->getConsumerScript()
				. $this->getUserBoxContainer();
	}
	
	
	/**
	 * Cast object into string.
	 * 
	 * @return string
	 */
	public function __toString() {
		try {
			return $this->userBox();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	
	/**
	 * Get container of the user box.
	 * 
	 * @param string $content HTML content
	 * @return string
	 */
	protected function getUserBoxContainer($content = '') {
		return sprintf(self::TEMPLATE_BOX_CONTAINER,
			(int)$this->getRublon()->isConfigured(),
			(int)$this->getRublon()->canUserActivate(),
			(string)$content
		)
		. new RublonDeviceWidget()
		. new RublonShareAccessWidget();
	}

	
}
