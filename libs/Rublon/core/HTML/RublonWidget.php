<?php

abstract class RublonWidget {
	

	// Device Widget CSS attributes.
	const WIDGET_CSS_FONT_COLOR = 'font-color';
	const WIDGET_CSS_FONT_SIZE = 'font-size';
	const WIDGET_CSS_FONT_FAMILY = 'font-family';
	const WIDGET_CSS_BACKGROUND_COLOR = 'background-color';
	

	/**
	 * Get iframe to load the Device Widget.
	 *
	 * @return string
	 */
	function __toString() {
		return '<iframe '. self::createAttributesString(array_merge(
			$this->getWidgetAttributes(),
			$this->getWidgetCSSAttribsData()
		)) .'></iframe>';
	}
	
	

	/**
	 * Creates HTML attributes string.
	 *
	 * @param array $attr
	 * @return string
	 */
	static function createAttributesString($attr) {
		$result = '';
		foreach ($attr as $name => $value) {
			$result .= ' ' . htmlspecialchars($name) .'="'. htmlspecialchars($value) .'"';
		}
		return $result;
	}
	
	

	/**
	 * Creates HTML attributes array for a widget CSS attributes.
	 *
	 * @return array
	 */
	private function getWidgetCSSAttribsData() {
		$result = array();
		$attribs = $this->getWidgetCSSAttribs();
		foreach ($attribs as $name => $value) {
			$result['data-' . $name] = $value;
		}
		return $result;
	}
	
	
	/**
	 * Returns CSS attributes for a widget.
	 *
	 * @return array
	 */
	protected function getWidgetCSSAttribs() {
		return array();
	}
	
	
	/**
	 * Get widget's iframe HTML attributes.
	 * 
	 * @return array
	 */
	protected function getWidgetAttributes() {
		return array();
	}
	
	
}
