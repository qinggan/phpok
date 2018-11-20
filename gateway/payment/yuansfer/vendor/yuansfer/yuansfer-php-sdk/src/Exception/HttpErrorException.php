<?php

namespace Yuansfer\Exception;

use Httpful\Response;

class HttpErrorException extends \Exception implements YuansferException
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * HttpErrorException constructor.
     *
     * @param Response|string $response
     * @param int $code
     */
    public function __construct($response = 'Service Unavailable', $code = 503)
    {
        if ($response instanceof Response) {
            $this->response = $response;

            $code = $response->code;
            $body = $response->raw_body;

            $message = "Invalid response from API: $body (HTTP response code was $code)";
        } else {
            $message = $response;
        }

        parent::__construct($message, $code);
    }

    public function getResponse()
    {
        return $this->response;

    }
}