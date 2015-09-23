<?php

/**
 * Rublon button class.
 * 
 * This class can be utilized to prepare a HTML container
 * for the Rublon buttons. The containers embedded in the website
 * will be filled with proper Rublon buttons once the consumer script
 * is executed.
 * 
 * @see RublonConsumerScript
 */
class RublonButton {
	

	/**
	 * Default CSS class of the button.
	 */
	const ATTR_CLASS = 'rublon-button';
	
	/**
	 * Prefix of the button's CSS "size" class.
	 */
	const ATTR_CLASS_SIZE_PREFIX = 'rublon-button-size-';

	/**
	 * Prefix of the button's CSS "color" class.
	 */
	const ATTR_CLASS_COLOR_PREFIX = 'rublon-button-color-';
	
	/**
	 * HTML attribute name to put the consumer params.
	 */
	const ATTR_CONSUMER_PARAMS = 'data-rublonconsumerparams';
	
	
	
	// Available sizes
	const SIZE_MINI = 'mini';
	const SIZE_SMALL = 'small';
	const SIZE_MEDIUM = 'medium';
	const SIZE_LARGE = 'large';
	
	// Available colors
	const COLOR_DARK = 'dark';
	const COLOR_LIGHT = 'light';
	
	
	
	/**
	 * Rublon instance.
	 * 
	 * An istance of the Rublon class or its descendant. Necessary
	 * for the class to work.
	 *
	 * @var Rublon
	 */
	protected $rublon = null;
	
	
	
	/**
	 * Label of the button.
	 * 
	 * Label displayed on the button and as its "title" attribute.
	 * 
	 * @var string $label 
	 */
	protected $label = null;
	
	/**
	 * Size of the button.
	 * 
	 * One of the predefined button size constants.
	 * 
	 * @var string $size
	 */
	protected $size = null;
	
	/**
	 * Color of the button.
	 * 
	 * One of the predefined button color constants.
	 * 
	 * @var string $color
	 */
	protected $color = null;
	
	
	
	/**
	 * HTML attributes of the button's container.
	 * 
	 * Any additional HTML attributes that will be added to the
	 * button upon its creation, e.g. class, style, data-attributes.
	 * 
	 * @var array $attributes
	 */
	protected $attributes = array();
	
	
	/**
	 * HTML content of the button.
	 * 
	 * @var string
	 */
	protected $content = '<a href="https://rublon.com/">Rublon</a>';
	
	
	/**
	 * Initialize object with Rublon instance.
	 *
	 * A Rublon class instance is required for
	 * the object to work.
	 *
	 * @param RublonConsumer $rublon An instance of the Rublon class
	 */
	public function __construct(RublonConsumer $rublon) {
		$rublon->log(__METHOD__);
		$this->rublon = $rublon;
		$this->setSize(self::SIZE_MEDIUM);
		$this->setColor(self::COLOR_DARK);
	}
	
	
	/**
	 * Convert object into string.
	 * 
	 * Returns HTML container of the button that can be
	 * embedded in the website.
	 * 
	 * @return string
	 */
	public function __toString() {
		$this->getRublon()->log(__METHOD__);
		
		$attributes = $this->attributes;
		
		$buttonClass = self::ATTR_CLASS;
		if (isset($attributes['class'])) {
			$attributes['class'] = $buttonClass . ' ' . $attributes['class'];
		} else {
			$attributes['class'] = $buttonClass;
		}
		
		$attributes['class'] .= ' ' . self::ATTR_CLASS_SIZE_PREFIX . $this->getSize();
		$attributes['class'] .= ' ' . self::ATTR_CLASS_COLOR_PREFIX . $this->getColor();
		
		if ($title = $this->getLabel()) {
			$attributes['title'] = $title;
		}
		
		$result = '<div';
		foreach ($attributes as $name => $val) {
			$result .= ' ' . $name . '="' . htmlspecialchars($val) . '"';
		}
		$result .= '>' . $this->getContent() . '</div>';
		
		return $result;
		
	}
	
	
	/**
	 * Get HTML content of the button.
	 * 
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}
	
	
	/**
	 * Set HTML content of the button.
	 * 
	 * @param string $content
	 * @return RublonButton
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}
	
	
	/**
	 * Set label of the button.
	 * 
	 * Button label property setter.
	 * 
	 * @param string $label Text to be set as the button's label.
	 * @return RublonButton
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}
	
	/**
	 * Get label of the button.
	 * 
	 * Button label property getter.
	 * 
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	
	/**
	 * Set size of the button.
	 * 
	 * Button size property setter.
	 * Get available size from RublonButton::SIZE_... constant.
	 * 
	 * @param string $size One of the button size constants.
	 * @return RublonButton
	 */
	public function setSize($size) {
		$this->size = $size;
		return $this;
	}
	
	/**
	 * Get size of the button.
	 * 
	 * Button size property getter. 
	 * 
	 * @return string
	 */
	public function getSize() {
		return $this->size;
	}
	
	
	/**
	 * Set color of the button.
	 *
	 * Button color property setter.
	 * Get available color from RublonButton::COLOR_... constant.
	 *
	 * @param string $color One of the button color constants.
	 * @return RublonButton
	 */
	public function setColor($color) {
		$this->color = $color;
		return $this;
	}
	
	
	/**
	 * Get color of the button.
	 * 
	 * Button color property getter.
	 * 
	 * @return string
	 */
	public function getColor() {
		return $this->color;
	}
	
	
	/**
	 * Set HTML attribute of the button's container.
	 * 
	 * Add a single HTML attribute to the button's container.
	 * 
	 * @param string $name Attribute's name
	 * @param string $value Attribute's value
	 * @return RublonButton
	 */
	public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
		return $this;
	}
	
	
	/**
	 * Get HTML attribute of the button's container.
	 * 
	 * Returns the button's container single HTML attribute.
	 * Null if the attribute doesn't exist.
	 * 
	 * @param string $name Attribute's name
	 * @return string|NULL
	 */
	public function getAttribute($name) {
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		} else {
			return null;
		}
	}
	

	/**
	 * Get Rublon instance.
	 * 
	 * Returns the Rublon instance.
	 *
	 * @return Rublon
	 */
	public function getRublon() {
		return $this->rublon;
	}
	
	
}
