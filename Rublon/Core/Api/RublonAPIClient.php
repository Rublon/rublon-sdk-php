<?php

namespace Rublon\Core\Api;

use Rublon\Core\Exceptions\ApiExceptionFactory;
use Rublon\Core\Exceptions\Client\EmptyErrorResponseField_RublonClientException;
use Rublon\Core\Exceptions\Client\EmptyResponse_RublonClientException;
use Rublon\Core\Exceptions\Client\EmptyResponseField_RublonClientException;
use Rublon\Core\Exceptions\Client\ErrorResponse_RublonClientException;
use Rublon\Core\Exceptions\Client\InvalidJSON_RublonClientException;
use Rublon\Core\Exceptions\Client\InvalidResponse_RublonClientException;
use Rublon\Core\Exceptions\Client\InvalidResponseHTTPStatusCode_RublonClientException;
use Rublon\Core\Exceptions\Client\InvalidSignature_RublonClientException;
use Rublon\Core\Exceptions\Client\MissingField_RublonClientException;
use Rublon\Core\Exceptions\Client\MissingHeader_RublonClientException;
use Rublon\Core\Exceptions\Client\RublonClientException;

use Rublon\Core\Exceptions\RublonException;
use Rublon\Core\RublonConsumer;

/**
 * Rublon API Client.
 *
 */
class RublonAPIClient {

	/**
	 * Connection timeout in seconds.
	 */
	const TIMEOUT = 30; // sec

	/**
	 * Hash algorithm name to compute the user's email hash.
	 */
	const HASH_ALG = 'sha256';

	/**
	 * User agent string.
	 */
	const USER_AGENT = 'rublon-php-sdk';

	/**
	 * HTTP Content-type header.
	 */
	const HEADER_CONTENT_TYPE = "Content-Type: application/json";

	/**
	 * HTTP Accept header.
	 */
	const HEADER_ACCEPT = "Accept: application/json, text/javascript, */*; q=0.01";

	const HEADER_TECHNOLOGY = "X-Rublon-Technology";
	const HEADER_SIGNATURE = 'X-Rublon-Signature';
	const HEADER_API_VERSION = 'X-Rublon-API-Version';
	const HEADER_API_VERSION_DATE = 'X-Rublon-API-Version-Date';

	const FIELD_ACCESS_TOKEN = 'accessToken';
	const FIELD_SYSTEM_TOKEN = 'systemToken';
    const FIELD_USERNAME = 'username';
	const FIELD_CALLBACK_URL = 'callbackUrl';
	const FIELD_STATUS = 'status';
	const FIELD_RESULT = 'result';
	const FIELD_ERROR_MSG = 'errorMessage';
	const FIELD_EXCEPTION = 'exception';
	const FIELD_NAME = 'name';
	const FIELD_USING_EMAIL2FA = 'usingEmail2FA';
	const FIELD_ACCESS_CONTROL_MANAGER_ALLOWED = 'accessControlManagerAllowed';
	const FIELD_APP_VERSION = 'appVer';
	const FIELD_PARAMS = 'params';

	const STATUS_OK = 'OK';
	const STATUS_ERROR = 'ERROR';

	/**
	 * Path to the pem certificates.
	 */
	const PATH_CERT = 'cert/cacert.pem';

	/**
	 * Rublon instance.
	 *
	 * @var RublonConsumer
	 */
	protected $rublon = null;

	/**
	 * Request URL.
	 *
	 * @var string
	 */
	protected $url = null;

	/**
	 * Request POST params.
	 *
	 * @var array
	 */
	protected $params = array();

	protected $rawPostBody = null;

	protected $rawRequestHeader = null;


	/**
	 * Raw response string.
	 *
	 * @var string
	 */
	protected $rawResponse = null;
	protected $responseHTTPStatus = null;
	protected $responseHTTPStatusCode = null;
	protected $rawResponseHeader = null;
	protected $responseHeaders = array();
	protected $rawResponseBody = null;

	/**
	 * Response data.
	 *
	 * @var array
	 */
	protected $response = null;


	/**
	 * Constructor.
	 *
	 * @param RublonConsumer $rublon
	 */
	public function __construct(RublonConsumer $rublon)
    {
		$rublon->log(__METHOD__);
		$this->rublon = $rublon;
	}

	/**
	 * Perform the request.
	 *
	 * @throws RublonException
	 * @return RublonAPIClient
	 */
	public function perform()
    {
		$this->getRublon()->log(__METHOD__);

		$this->performRequest();

		try {
			$this->validateResponse();
		} catch (RublonException $e) {
			throw $e;
		}

		return $this;
	}

	protected function validateResponse()
    {
		if ($this->responseHTTPStatusCode == 200 || $this->responseHTTPStatusCode == 400) {
			if (!empty($this->rawResponseBody)) {
				$this->response = json_decode($this->rawResponseBody, true);
				if (!empty($this->response) AND is_array($this->response)) {
					if (!empty($this->response[self::FIELD_STATUS])) {
						if ($this->response[self::FIELD_STATUS] == self::STATUS_OK) {
							if ($signature = $this->getHeader(self::HEADER_SIGNATURE)) {
								if ($this->validateSignature($signature, $this->rawResponseBody)) {
									return true;
								} else throw new InvalidSignature_RublonClientException($this, 'Invalid response signature: '. $signature);
							} else throw new MissingHeader_RublonClientException($this, self::HEADER_SIGNATURE);
						}
						else if ($this->response[self::FIELD_STATUS] == self::STATUS_ERROR) {
							if (!empty($this->response[self::FIELD_RESULT])) {
								if (!empty($this->response[self::FIELD_RESULT][self::FIELD_EXCEPTION])) {
									throw $this->constructException($this->response[self::FIELD_RESULT]);
								}
								else if (!empty($this->response[self::FIELD_RESULT][self::FIELD_ERROR_MSG])) {
									throw new ErrorResponse_RublonClientException($this, $this->response[self::FIELD_RESULT][self::FIELD_ERROR_MSG]);
                                } else throw new EmptyErrorResponseField_RublonClientException($this, 'Error result with empty field `'.self::FIELD_ERROR_MSG.'`');
                            } else throw new EmptyResponseField_RublonClientException($this, 'Empty response field('.self::FIELD_RESULT.')');
                        } else throw new InvalidResponse_RublonClientException($this, 'Invalid response status ('. $this->response[self::FIELD_STATUS].')');
                    } else throw new MissingField_RublonClientException($this, 'Missing field `'.self::FIELD_STATUS.'`');
                } else throw new InvalidJSON_RublonClientException($this, 'Invalid API response');
            } else throw new EmptyResponse_RublonClientException($this, 'Invalid API response');
        } else throw new InvalidResponseHTTPStatusCode_RublonClientException($this, 'Invalid API response HTTP Status Code `'.$this->responseHTTPStatusCode.'`');
    }

	protected function constructException(array $data)
    {
		$arg = null;
		if (!empty($data[self::FIELD_NAME])) {
			$arg = $data[self::FIELD_NAME];
		} else if (!empty($data[self::FIELD_ERROR_MSG])) {
			$arg = $data[self::FIELD_ERROR_MSG];
		}

        $className = $data[self::FIELD_EXCEPTION];
        return ApiExceptionFactory::createApiException($className, $this, $arg);
	}

	protected function getHeader($name)
    {
		if (isset($this->responseHeaders[$name])) {
			return $this->responseHeaders[$name];
		}

		// sometimes, somehow Rublon X-Credentials are coming with lowercase letter
        // so we must handle that situation as follow
        if (isset($this->responseHeaders[strtolower($name)])) {
            return $this->responseHeaders[strtolower($name)];
        }
	}

	/**
	 * Perform a request and set rawResponse field.
	 *
	 * @throws RublonException
	 */
	protected function performRequest()
    {
		$this->getRublon()->log(__METHOD__);

		if (empty($this->rawPostBody) AND !empty($this->params)) {
			$this->rawPostBody = json_encode($this->params);
		}
		$response = $this->request($this->url, $this->rawPostBody);

		$this->rawResponse = implode('', $response);

		$this->getRublon()->log($this->rawResponse);

		$this->rawResponseHeader = trim(array_shift($response));
		$this->rawResponseBody = trim(array_shift($response));

		$header = explode("\n", $this->rawResponseHeader);
		$this->responseHTTPStatus = array_shift($header);
		preg_match('/^HTTP\/\d\.\d (\d+)/', $this->responseHTTPStatus, $match);
		if (isset($match[1])) {
			$this->responseHTTPStatusCode = $match[1];
		}

		// When preg_match pattern didn't match anything somehow
		if (empty($this->responseHTTPStatusCode)) {
            if (function_exists('http_response_code')) {
                $http_response_code = http_response_code();
                if (!empty($http_response_code)) {
                    $this->responseHTTPStatusCode = http_response_code();
                }
            }
        }

		foreach ($header as $headerLine) {
			if (strpos($headerLine, ':') !== false) {
				list($name, $value) = explode(':', $headerLine, 2);
				$this->responseHeaders[trim($name)] = trim($value);
			}
		}

		return $this;
	}

	/**
	 * Set parameters of the request.
	 *
	 * @param array $params
	 * @return RublonRequest
	 */
	public function setRequestParams(array $params)
    {
		if (!is_array($params)) $params = array();
		$this->params = $params;
		return $this;
	}

	public function addRequestParams(array $params)
    {
		foreach ($params as $name => $field) {
			$this->params[$name] = $field;
		}
		return $this;
	}

	/**
	 * Set the URL of the request.
	 *
	 * @param string $url
	 * @return RublonAPIClient
	 */
	public function setRequestURL($url)
    {
		$this->url = $url;
		return $this;
	}

	/**
	 * Get raw response body string.
	 *
	 * @return string
	 */
	public function getRawResponseBody()
    {
		return $this->rawResponseBody;
	}

	public function getRawResponseHeader()
    {
		return $this->rawResponseHeader;
	}

	public function getRawRequest()
    {
		return $this->getRawRequestHeader() . $this->getRawRequestBody();
	}

	public function getRawRequestHeader()
    {
		return $this->rawRequestHeader;
	}

	public function getRawRequestBody()
    {
		return $this->rawPostBody;
	}

	/**
	 * Get raw response string.
	 *
	 * @return string
	 */
	public function getRawResponse()
    {
		return $this->rawResponse;
	}

	/**
	 * Get parsed response data.
	 *
	 * @return array
	 */
	public function getResponse()
    {
		return $this->response;
	}

	/**
	 * Perform HTTP request.
	 *
	 * @param string $url URL address
	 * @param string $rawPostBody
     *
	 * @return string Response
	 * @throws RublonException
	 */
	protected function request($url, $rawPostBody = null)
    {
		$this->getRublon()->log(__METHOD__ . ' -- ' . $url);
        
		if (!function_exists('curl_init')) {
			throw new RublonClientException($this, 'cURL functions are not available.', RublonClientException::CODE_CURL_NOT_AVAILABLE);
		}

		$ch = curl_init($url);
		$headers = array(
			self::HEADER_CONTENT_TYPE,
			self::HEADER_ACCEPT,
			sprintf('%s: %s', self::HEADER_SIGNATURE, $this->signMessage($rawPostBody)),
			sprintf('%s: %s', self::HEADER_TECHNOLOGY, $this->getRublon()->getTechnology()),
			sprintf('%s: %s', self::HEADER_API_VERSION, $this->getRublon()->getVersion()),
			sprintf('%s: %s', self::HEADER_API_VERSION_DATE, $this->getRublon()->getVersionDate()),
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$curl_timeout = self::TIMEOUT;
		$php_execution_time = ini_get('max_execution_time');
		if (!empty($php_execution_time) && is_numeric($php_execution_time)) {
			if ($php_execution_time < 36 && $php_execution_time > 9) {
				$curl_timeout = $php_execution_time - 5;
			} elseif ($php_execution_time < 10) {
				$curl_timeout = 5;
			}
		}

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $curl_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $curl_timeout);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

		// POST body
		if (!empty($rawPostBody)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $rawPostBody);
			$this->getRublon()->log($rawPostBody);
		}

		// SSL options
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		if (function_exists('ini_get') AND ini_get('curl.cainfo')) {
			// cURL CAinfo from PHP ini
		} else {
			curl_setopt($ch, CURLOPT_CAINFO, $this->getCertPath());
		}

		// Execute request
		$response = curl_exec($ch);

		$this->rawRequestHeader = curl_getinfo($ch, CURLINFO_HEADER_OUT );

		if ($error = curl_error($ch)) {
			$errno = curl_errno($ch);
			curl_close($ch);
			throw new RublonClientException($this, $error .' ('. $errno .')', RublonClientException::CODE_CURL_ERROR);
		} else {
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $headerSize);
			$body = substr($response, $headerSize, strlen($response));
			curl_close($ch);

			return array($header, $body);
		}
	}

	protected function validateSignature($signature, $input, $secret = null)
    {
		$check = $this->signMessage($input, $secret);
		return ($check == $signature);
	}

	protected function signMessage($data, $secret = null)
    {
		if (is_null($secret)) {
			$secret = $this->getRublon()->getSecretKey();
		}
		return hash_hmac(self::HASH_ALG, $data, $secret);
	}

	/**
	 * Get absolute path to the pem certificates.
	 *
	 * @return string
	 */
	protected function getCertPath()
    {
		$ds = DIRECTORY_SEPARATOR;
		$certPath = explode($ds, __FILE__);
		array_pop($certPath);
		array_pop($certPath);
		array_pop($certPath);
		$certPath = implode($ds, $certPath) . $ds . str_replace('/', DIRECTORY_SEPARATOR, self::PATH_CERT);
		return $certPath;
	}

	/**
	 * Get the Rublon instance.
	 *
	 * @return RublonConsumer
	 */
	public function getRublon()
    {
		return $this->rublon;
	}
}
