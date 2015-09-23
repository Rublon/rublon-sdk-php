<?php

/**
 * Exception class.
 * 
 */
class RublonException extends Exception {

	/**
	 * CURL library is not available.
	 */
	const CODE_CURL_NOT_AVAILABLE = 1;
	
	/**
	 * Invalid response.
	 */
	const CODE_INVALID_RESPONSE = 2;
	
	/**
	 * Response error.
	 */
	const CODE_RESPONSE_ERROR = 3;
	
	/**
	 * CURL error.
	 */
	const CODE_CURL_ERROR = 4;
	
	/**
	 * Connection error.
	 */
	const CODE_CONNECTION_ERROR = 5;

	/**
	 * Timestamp error.
	 */
	const CODE_TIMESTAMP_ERROR = 6;
	
	/**
	 * Invalid access token.
	 */
	const CODE_INVALID_ACCESS_TOKEN = 7;


	/**
	 * For backward compatibility: manually store previous exception.
	 *
	 * @var Exception
	 */
	protected $previous = null;


	/**
	 * Constructor.
	 *
	 * @param string $msg (optional)
	 * @param int $code (optional)
	 * @param Exception $prev (optional)
	 */
	public function __construct($msg = "", $code = 0, Exception $prev = null) {

		// For backward compatibility check if getPrevious() method exists
		if (method_exists($this, 'getPrevious')) {
			parent::__construct($msg, $code, $prev);
		} else {
			parent::__construct($msg, $code);
			$this->previous = $prev;
		}
	}

	/**
	 * Handler for non-existing methods.
	 * 
	 * @param string $method
	 * @param array $args
	 * @return Exception
	 */
	public function __call($method, $args = array()) {
		// For backward compatibility handle non-existing method getPrevious()
		if ($method == 'getPrevious') {
			return $this->previous;
		}
	}

}
