<?php

namespace Rublon\Core\Api;

use Rublon\Core\RublonConsumer;
use Rublon\Core\Exceptions\RublonException;

/**
 * API request: Credentials.
 *
 */
class RublonAPICheckApplication extends RublonAPIClient
{

    /**
     * URL path of the request.
     *
     * @var string
     */
    protected $urlPath = '/api/app/init';

    /**
     * Constructor.
     *
     * @param RublonConsumer $rublon
     * @param string $appVer
     * @param array $params
     * @throws RublonException
     */
    public function __construct(RublonConsumer $rublon, string $appVer, $params = []) {

        parent::__construct($rublon);

        // Set request URL and parameters
        $url = $rublon->getAPIDomain() . $this->urlPath;
        $data = array(
            self::FIELD_SYSTEM_TOKEN => $rublon->getSystemToken(),
            self::FIELD_APP_VERSION => $appVer,
            self::FIELD_PARAMS => $params
        );

        $this->setRequestURL($url)->setRequestParams($data);
    }

}
