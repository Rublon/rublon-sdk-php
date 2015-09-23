<?php

/**
 * Signature wrapper for input and output data.
 *
 * Body of the message is signed by the HMAC-SHA256 hash of the string formed of
 * concatenation of the consumer's secret key and the body string.
 * Body and its signature are wrapped into a JSON structure.
 *
 * To verify the input message it's necessary to compute the HMAC-SHA256 hash
 * of the consumer's secret key concatenated with the message body string
 * and compare with the signature of the message.
 * 
 */
class RublonSignatureWrapper {
	

	/**
	 * Rublon message life time
	 */
	const MESSAGE_LIFETIME = 300;
	
	/**
	 * Hash algorithm name for HMAC.
	 */
	const HASH_ALG = 'SHA256';
	
	/**
	 * Field name for wrapper data.
	 */
	const FIELD_DATA = 'data';
	
	/**
	 * Field name for message body.
	 */
	const FIELD_BODY = 'body';
	
	/**
	 * Field name for message header.
	 */
	const FIELD_HEAD = 'head';
	
	/**
	 * Field name for size.
	 */
	const FIELD_HEAD_SIZE = 'size';

	/**
	 * Field name for size.
	 */
	const FIELD_HEAD_TIME = 'time';
	
	/**
	 * Field name for signature.
	 */
	const FIELD_SIGN = 'sign';
	
	/**
	 * Field name for status.
	 */
	const FIELD_STATUS = 'status';
	
	/**
	 * Field name for message content.
	 */
	const FIELD_MSG = 'msg';
	
	/**
	 * Error status.
	 */
	const STATUS_ERROR = 'ERROR';
	
	/**
	 * Config key to skip time validation.
	 */
	const CONFIG_SKIP_TIME = 'skipTime';

	
	/**
	 * Secret key for verifying signature.
	 *
	 * @var string
	 */
	protected $secretKey = null;

	/**
	 * Body of data.
	 *
	 * @var array
	 */
	protected $body = null;

	/**
	 * Raw data string.
	 *
	 * @var string
	 */
	protected $rawData = null;




	/**
	 * Get object's string - JSON with signed data.
	 *
	 * @return string
	 */
	public function __toString() {
		return json_encode($this->getWrapper());
	}



	/**
	 * Set raw input.
	 *
	 * @param string $input
	 * @return RublonSignatureWrapper
	 */
	public function setInput($input) {
		$this->rawData = $input;
		@ $data = json_decode($input, true);
		@ $data = json_decode($data[self::FIELD_DATA], true);
		@ $this->body = json_decode($data[self::FIELD_BODY], true);
		return $this;
	}



	/**
	 * Set secret key.
	 *
	 * @param string $secretKey
	 * @return RublonSignatureWrapper
	 */
	public function setSecretKey($secretKey) {
		$this->secretKey = $secretKey;
		return $this;
	}


	/**
	 * Set body of data.
	 *
	 * @param array $body
	 * @return RublonSignatureWrapper
	 */
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}


	/**
	 * Get body data.
	 *
	 * @return array
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * Get wrapper with data and signature generated from body.
	 *
	 * @return array
	 */
	public function getWrapper() {
		return self::wrap($this->secretKey, $this->body);
	}





	// ------------------------------------------------------------------------------------------------------------------------------
	// Static methods
	// ------------------------------------------------------------------------------------------------------------------------------


	/**
	 * Verify data by signature and secret key.
	 *
	 * @param mixed $data Data to sign
	 * @param string $secretKey Secret key used to create the signature
	 * @param string $sign Computed signature
	 * @return bool
	 */
	public static function verifyData($data, $secretKey, $sign) {
		$dataSign = self::signData($data, $secretKey);
		return ($dataSign == $sign);
	}




	/**
	 * Sign data by secret key.
	 *
	 * @param string $data Data to sign
	 * @param string $secretKey Secret key to create the signature
	 * @return string
	 */
	public static function signData($data, $secretKey) {
		return hash_hmac(self::HASH_ALG, $data, $secretKey);
	}



	/**
	 * Wrap string message into wrapper with signature.
	 *
	 * @param string $secretKey Secret key used to create a signature
	 * @param string|array $body Body of the message
	 * @return array Wrapper with signature and data fields (data is JSON with head and body fields)
	 */
	public static function wrap($secretKey, $body) {

		if (!is_string($body)) $body = json_encode($body);

		$data = array();

		$data[self::FIELD_HEAD] = array(
			self::FIELD_HEAD_SIZE => strlen($body),
			self::FIELD_HEAD_TIME => time(),
		);
		$data[self::FIELD_BODY] = $body;

		$data = json_encode($data);

		return array(
			self::FIELD_DATA => $data,
			self::FIELD_SIGN => self::signData($data, $secretKey),
		);

	}

	

	/**
	 * Parse signed message.
	 *
	 * @throws Exception
	 * @param mixed $jsonStr
	 * @param string $secretKey
	 * @param array $config
	 * @return mixed
	 */
	static function parseMessage($jsonStr, $secretKey, $config = array()) {
		
		if (empty($secretKey)) {
			throw new RublonException('Empty secret');
		}
		if (empty($jsonStr)) {
			throw new RublonException('Empty response', RublonException::CODE_INVALID_RESPONSE);
		}
		
		// Verify response JSON
		$response = json_decode($jsonStr, true);
		if (empty($response)) {
			throw new RublonException('Invalid response: '. $jsonStr, RublonException::CODE_INVALID_RESPONSE);
		}
		if (!empty($response[self::FIELD_STATUS]) AND $response[self::FIELD_STATUS] == self::STATUS_ERROR) {
			$msg = isset($response[self::FIELD_MSG]) ? $response[self::FIELD_STATUS] : 'Error response: '. $jsonStr;
			throw new RublonException($msg, RublonException::CODE_INVALID_RESPONSE);
		}
		if (empty($response[self::FIELD_DATA])) {
			throw new RublonException('Missing data field', RublonException::CODE_INVALID_RESPONSE);
		}
		if (empty($response[self::FIELD_SIGN])) {
			throw new RublonException('Missing sign field', RublonException::CODE_INVALID_RESPONSE);
		}
		if (!RublonSignatureWrapper::verifyData($response[self::FIELD_DATA], $secretKey, $response[self::FIELD_SIGN])) {
			throw new RublonException('Invalid signature', RublonException::CODE_INVALID_RESPONSE);
		}
		
		// Verify data field
		$data = json_decode($response[self::FIELD_DATA], true);
		if (empty($data) OR !is_array($data)) {
			throw new RublonException('Invalid response', RublonException::CODE_INVALID_RESPONSE);
		}
		if (!isset($data[self::FIELD_HEAD]) OR !is_array($data[self::FIELD_HEAD]) OR empty($data[self::FIELD_HEAD])) {
			throw new RublonException('Invalid response data (invalid header)', RublonException::CODE_INVALID_RESPONSE);
		}
		
		// Verify head field
		$head = $data[self::FIELD_HEAD];
		if (empty($config[self::CONFIG_SKIP_TIME]) AND !(isset($head[self::FIELD_HEAD_TIME]) AND abs(time() - $head[self::FIELD_HEAD_TIME]) <= self::MESSAGE_LIFETIME)) {
			throw new RublonException('Invalid message time', RublonException::CODE_TIMESTAMP_ERROR);
		}
		if (!isset($data[self::FIELD_BODY]) OR !is_string($data[self::FIELD_BODY])) {
			throw new RublonException('Invalid response data (no body)', RublonException::CODE_INVALID_RESPONSE);
		}
		
		// Verify body field
		$body = json_decode($data[self::FIELD_BODY], true);
		if (is_array($body) AND !empty($body)) {
			return $body;
		} else {
			return $data[self::FIELD_BODY];
		}
		
	}
	
	


	/**
	 * Generate random string.
	 *
	 * @param int $len (optional)
	 * @return string
	 */
	static public function generateRandomString($len = 100) {
		$chars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		$max = strlen($chars) - 1;
		$result = '';
		for ($i=0; $i<$len; $i++) {
			$result .= $chars[mt_rand(0, $max)];
		}
		return $result;
	}


}
