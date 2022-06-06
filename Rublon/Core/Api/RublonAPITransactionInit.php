<?php

namespace Rublon\Core\Api;

use Rublon\Core\Exceptions\Client\MissingField_RublonClientException;
use Rublon\Core\RublonConsumer;

/**
 * API request: Init Transaction.
 *
 */
class RublonAPITransactionInit extends RublonAPIClient {
	
	/**
	 * URL path of the request.
	 *
	 * @var string
	 */
    const URL_PATH = '/api/transaction/init';

    const FIELD_CALLBACK_URL = 'callbackUrl';
    const FIELD_PARAMS       = 'params';
    const FIELD_PASSWORDLESS = 'isPasswordless';
    const FIELD_SYSTEM_TOKEN = 'systemToken';
	const FIELD_USERNAME     = 'username';
	const FIELD_USER_EMAIL   = 'userEmail';
	const FIELD_WEB_URI      = 'webURI';

	/**
	 * Constructor.
	 *
	 * @param RublonConsumer $rublon
	 * @param string $callbackUrl Callback URL address.
     * @param string $userEmail User's email address.
	 * @param string $username User's username in local system.
	 * @param array $params Custom parameters array (optional).
     * @param boolean $isPasswordless param for passwordless authentication
	 */
	public function __construct(RublonConsumer $rublon, $callbackUrl, $userEmail, $username, array $params = [], $isPasswordless = false)
    {
		parent::__construct($rublon);

		if ( ! $rublon->isConfigured()) {
			trigger_error(RublonConsumer::TEMPLATE_CONFIG_ERROR, E_USER_ERROR);
		}

        $data = [
            self::FIELD_SYSTEM_TOKEN => $rublon->getSystemToken(),
            self::FIELD_USERNAME     => $username,
            self::FIELD_CALLBACK_URL => $callbackUrl,
            self::FIELD_PASSWORDLESS => $isPasswordless
        ];

        if ( ! empty($userEmail)) {
            $data[self::FIELD_USER_EMAIL] = strtolower($userEmail);
        }

        if ( ! empty($params)) {
            $data[self::FIELD_PARAMS] = $params;
        }

		// Set request URL and parameters
		$url = $rublon->getAPIDomain() . self::URL_PATH;
		$this->setRequestURL($url)->setRequestParams($data);
	}

	/**
	 * Returns URI to redirect to.
	 * 
	 * @return string
	 */
	public function getWebURI()
    {
		return $this->response[self::FIELD_RESULT][self::FIELD_WEB_URI];
	}

	/**
	 * (non-PHPdoc)
	 * @see RublonAPIClient::validateResponse()
	 */
	protected function validateResponse()
    {
		if (parent::validateResponse()) {
			if ( ! empty($this->response[self::FIELD_RESULT][self::FIELD_WEB_URI])) {
				return true;
			} else throw new MissingField_RublonClientException($this, self::FIELD_WEB_URI);
		}
	}
}
