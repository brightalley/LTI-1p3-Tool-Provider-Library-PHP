<?php

namespace ceLTIc\LTI\Service;

use ceLTIc\LTI;
use ceLTIc\LTI\ToolConsumer;
use ceLTIc\LTI\HTTPMessage;

/**
 * Class to implement a service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @version  3.1.0
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Service
{

    /**
     * Whether service request should be sent unsigned.
     *
     * @var bool $unsigned
     */
    public $unsigned = false;

    /**
     * Service endpoint.
     *
     * @var string $endpoint
     */
    protected $endpoint;

    /**
     * Tool Consumer for this service request.
     *
     * @var ToolConsumer $consumer
     */
    private $consumer;

    /**
     * Media type of message body.
     *
     * @var string $mediaType
     */
    private $mediaType;

    /**
     * HTTPMessage object for last service request.
     *
     * @var HTTPMessage|null $http
     */
    private $http = null;

    /**
     * Class constructor.
     *
     * @param ToolConsumer $consumer   Tool consumer object for this service request
     * @param string       $endpoint   Service endpoint
     * @param string       $mediaType  Media type of message body
     */
    public function __construct($consumer, $endpoint, $mediaType)
    {
        $this->consumer = $consumer;
        $this->endpoint = $endpoint;
        $this->mediaType = $mediaType;
    }

    /**
     * Send a service request.
     *
     * @param string  $method      The action type constant (optional, default is GET)
     * @param array   $parameters  Query parameters to add to endpoint (optional, default is none)
     * @param string  $body        Body of request (optional, default is null)
     *
     * @return HTTPMessage HTTP object containing request and response details
     */
    public function send($method, $parameters = array(), $body = null)
    {
        $url = $this->endpoint;
        if (!empty($parameters)) {
            if (strpos($url, '?') === false) {
                $sep = '?';
            } else {
                $sep = '&';
            }
            foreach ($parameters as $name => $value) {
                $url .= $sep . urlencode($name) . '=' . urlencode($value);
                $sep = '&';
            }
        }
        if (!$this->unsigned) {
            $header = $this->consumer->signServiceRequest($url, $method, $this->mediaType, $body);
        } else {
            $header = null;
        }

// Connect to tool consumer
        $this->http = new HTTPMessage($url, $method, $body, $header);
// Parse JSON response
        if ($this->http->send() && !empty($this->http->response)) {
            $this->http->responseJson = json_decode($this->http->response);
            $this->http->ok = !is_null($this->http->responseJson);
        }

        return $this->http;
    }

    /**
     * Get HTTPMessage object for last request.
     *
     * @return HTTPMessage HTTP object containing request and response details
     */
    public function getHTTPMessage()
    {
        return $this->http;
    }

}