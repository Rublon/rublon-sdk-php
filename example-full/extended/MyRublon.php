<?php

require_once dirname(__FILE__) . '/../../libs/Rublon/Rublon2Factor.php';

class MyRublon extends Rublon2Factor {
	
	
	public function getAPIDomain() {
		global $config;
		return $config['rublon']['apiDomain'];
	}
	
	
}