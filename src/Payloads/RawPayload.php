<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Payloads;

use Hayttp\Contracts\Payload as PayloadContract;
use Hayttp\Traits\HasCompleteDebugInfo;

/**
 * Raw (string) body Helper.
 */
class RawPayload implements PayloadContract
{
    use HasCompleteDebugInfo;

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * Constructor.
     *
     * @param string $contents
     * @param string $contentType
     */
    public function __construct(string $contents, string $contentType)
    {
        $this->contents = $contents;
        $this->contentType = $contentType;
    }

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    public function contentType() : string
    {
        return $this->contentType;
    }

    /**
     * Render into a http request body.
     */
    public function render() : string
    {
        return $this->contents;
    }

    /**
     * Render the body of the payload.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->render();
    }
}
