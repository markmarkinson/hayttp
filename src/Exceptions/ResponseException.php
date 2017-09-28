<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Exceptions;

use Exception;
use RuntimeException;
use Hayttp\Contracts\Request as RequestContract;
use Hayttp\Contracts\Response as ResponseContract;

/**
 * Http connection exception.
 *
 * Thrown when the response does not adhere to our expectations
 */
class ResponseException extends RuntimeException
{
    /**
     * @var ResponseContract
     */
    protected $response;

    /**
     * Constructor.
     */
    public function __construct(ResponseContract $response, $message, Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct(sprintf('Bad response: %s', $message), 0, $previous);
    }

    /**
     * Get the request that caused the bad response.
     *
     * @return RequestContract
     */
    public function getRequest() : RequestContract
    {
        return $this->response->request();
    }

    /**
     * Get the response that couldn't connect.
     *
     * @return ResponseContract
     */
    public function getResponse() : ResponseContract
    {
        return $this->response;
    }
}
