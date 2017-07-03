<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Util;
use Moccalotto\Hayttp\Exceptions\Response as R;
use Moccalotto\Hayttp\Exceptions\ResponseException;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

trait MakesResponseAssertions
{
    /**
     * Throw a ResponseException if $success is false.
     *
     * @param bool              $success
     * @param ResponseException $exception
     *
     * @return $this
     *
     * @throws ResponseException
     */
    protected function ensure($success, ResponseException $exception) : ResponseContract
    {
        if (!$success) {
            throw $exception;
        }

        return $this;
    }

    /**
     * Ensure that status code is in a given range.
     *
     * @param int $min
     * @param int $max
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureStatusInRange($min, $max) : ResponseContract
    {
        $success = $this->statusCode() >= $min && $this->statusCode() <= $max;

        return $this->ensure(
            $success,
            new R\StatusCodeException(
                $this,
                sprintf(
                    'Expected status code to be in range [%d...%d], but %d was returned',
                    $min,
                    $max,
                    $this->statusCode()
                )
            )
        );
    }

    /**
     * Ensure that the status code is in a given et of codes.
     *
     * @param int[] $validCodes
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureStatusIn(array $validCodes) : ResponseContract
    {
        return $this->ensure(
            in_array($this->statusCode(), $validCodes),
            new R\StatusCodeException(
                $this,
                sprintf(
                    'Expected status code to be one of [%s], but %d was returned',
                    implode($validCodes),
                    $this->statusCode()
                )
            )
        );
    }

    /**
     * Ensure that the status code equals $validCode.
     *
     * @param int $validCode
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureStatus($validCode) : ResponseContract
    {
        return $this->ensure(
            $this->statusCode() == $validCode,
            new R\StatusCodeException(
                $this,
                sprintf(
                    'Expected status code to be %d, but it was %d',
                    $validCode,
                    $this->statusCode()
                )
            )
        );
    }

    /**
     * Ensure that the status code is in the range [200...299].
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure2xx() : ResponseContract
    {
        return $this->ensureStatusInRange(200, 299);
    }

    /**
     * Ensure that the status code is 200.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure200() : ResponseContract
    {
        return $this->ensureStatus(200);
    }

    /**
     * Ensure that the status code is 201.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure201() : ResponseContract
    {
        return $this->ensureStatus(201);
    }

    /**
     * Ensure that the status code is 204.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure204() : ResponseContract
    {
        return $this->ensureStatus(204);
    }

    /**
     * Ensure that the status code is 301.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure301() : ResponseContract
    {
        return $this->ensureStatus(301);
    }

    /**
     * Ensure that the status code is 302.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure302() : ResponseContract
    {
        return $this->ensureStatus(302);
    }

    /**
     * Ensure that the content type is application/json.
     *
     * @param array|object $data
     * @param bool         $strict
     *
     * @return $this
     *
     * @throws R\ContentTypeException
     */
    public function ensureJson($data = [], bool $strict = true) : ResponseContract
    {
        $this->ensureContentType('application/json');

        if (empty($data)) {
            return $this;
        }

        $bodyArray = Util::recursiveArraySort(json_decode($this->body(), true));
        $dataArray = Util::recursiveArraySort(json_decode(json_encode($data), true));

        if (!is_array($bodyArray)) {
            throw new R\ContentException($this, 'Unparseable json in response body');
        }

        $replaced = array_replace_recursive($bodyArray, $dataArray);

        $exception = new R\ContentException($this, Util::makeExpectationMessage(
            'Could not find data subset in response',
            $dataArray,
            $bodyArray
        ));

        if ($strict && $replaced !== $bodyArray) {
            throw $exception;
        }

        if ($replaced != $bodyArray) {
            throw $exception;
        }

        return $this;
    }

    /**
     * Ensure that the content type is application/xml.
     *
     * @return $this
     *
     * @throws R\ContentTypeException
     */
    public function ensureXml() : ResponseContract
    {
        return $this->ensureContentType(['application/xml', 'text/xml']);
    }

    /**
     * Ensure that the response has a given content type.
     *
     * @param string|strings[] $contentType
     *
     * @return $this
     *
     * @throws R\ContentTypeException
     */
    public function ensureContentType($contentType) : ResponseContract
    {
        if (is_string($contentType)) {
            $contentType = [$contentType];
        }

        return $this->ensure(
            $this->hasContentType($contentType),
            new R\ContentTypeException(
                $this,
                sprintf(
                    'Expected response content type to be [%s], but it was %s',
                    implode('|', $contentType),
                    $this->contentType()
                )
            )
        );
    }

    /**
     * Ensure that the status code is in the range [200...299].
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureSuccess() : ResponseContract
    {
        return $this->ensure2xx();
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param string $url
     *
     * @return $this
     */
    public function ensureRedirect($url = null) : ResponseContract
    {
        return $this->ensureStatusIn([301, 302])
            ->ensureHeader('Location', $url);
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName
     * @param mixed  $expectedValue
     *
     * @return $this
     */
    public function ensureHeader($headerName, $expectedValue = null) : ResponseContract
    {
        $headerValue = $this->header($headerName);

        if ($headerValue === null) {
            throw new R\HeaderException($this, "Header $headerName is missing");
        }

        if ($expectedValue === null) {
            return $this;
        }

        if ($expectedValue === $headerValue) {
            return $this;
        }

        throw new R\HeaderException(
            $this,
            sprintf(
                'Header %s was expected to have the value %s, but it has the value %s',
                $headerName,
                $expectedValue,
                $headerValue
            )
        );
    }

    /**
     * Assert that the given string is contained within the response.
     *
     * @param string $value
     *
     * @return $this
     */
    public function ensureContains($value) : ResponseContract
    {
        if (strpos($this->body(), $value) === false) {
            throw new R\ContentException(
                $this,
                "Response body was expected to contain $value, but it does not"
            );
        }

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param string $value
     *
     * @return $this
     */
    public function ensureSeeText($value) : ResponseContract
    {
        if (strpos(strip_tags($this->body()), $value) === false) {
            throw new R\ContentException(
                $this,
                "The response text was expected to contain $value, but it does not"
            );
        }

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response.
     *
     * @param string $value
     *
     * @return $this
     */
    public function ensureDontSee($value) : ResponseContract
    {
        if (strpos($this->body(), $value) !== false) {
            throw new R\ContentException(
                $this,
                "Response body was expected to contain $value, but it does not"
            );
        }

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response text.
     *
     * @param string $value
     *
     * @return $this
     */
    public function ensureDontSeeText($value) : ResponseContract
    {
        if (strpos(strip_tags($this->body()), $value) !== false) {
            throw new R\ContentException(
                $this,
                "The response text was expected to contain $value, but it does not"
            );
        }

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param array $data
     *
     * @return $this
     */
    public function ensureExactJson(array $data) : ResponseContract
    {
        $bodyArray = json_encode(Util::recursiveArraySort(
            json_decode($this->body(), true)
        ));

        $dataArray = Util::recursiveArraySort($data);

        if ($bodyArray === $dataArray) {
            return $this;
        }

        throw new R\ContentException(
            $this,
            'Response body does not contain the specified data'
            . PHP_EOL
            . PHP_EOL
            . 'Expected: '
            . PHP_EOL
            . print_r($dataArray, true)
            . PHP_EOL
            . PHP_EOL
            . 'Actual: '
            . PHP_EOL
            . print_r($bodyArray, true)
            . PHP_EOL
        );
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param array $data
     *
     * @return $this
     */
    public function ensureJsonFragment(array $data) : ResponseContract
    {
        $actual = json_encode(Util::recursiveArraySort(
            (array) $this->jsonDecoded()
        ));

        foreach (Util::recursiveArraySort($data) as $key => $value) {
            $expected = substr(json_encode([$key => $value]), 1, -1);

            $error = Util::makeExpectationMessage(
                'Unable to find json fragment',
                $expected,
                $actual
            );

            if (strpos($actual, $expected) === false) {
                throw new R\ContentException($this, $error);
            }
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param array $data
     *
     * @return $this
     */
    public function ensureJsonMissing(array $data) : ResponseContract
    {
        $actual = json_encode(Util::recursiveArraySort(
            (array) $this->jsonDecoded()
        ));

        foreach (Util::recursiveArraySort($data) as $key => $value) {
            $expected = substr(json_encode([$key => $value]), 1, -1);

            $error = Util::makeExpectationMessage(
                'Found unexpected json fragment',
                $expected,
                $actual
            );

            if (strpos($actual, $expected) !== false) {
                throw new R\ContentException($this, $error);
            }
        }

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array|null $structure
     * @param array|null $data      Data to validate (needed for recursion). If null, the json body of the response
     *                              is used.
     *
     * @return $this
     */
    public function ensureJsonStructure(array $structure = null, $data = null) : ResponseContract
    {
        if (is_null($structure)) {
            return $this->ensureJson($this->jsonDecoded());
        }

        if (is_null($data)) {
            $data = $this->jsonDecoded();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                if (!is_array($data)) {
                    throw new R\ContentException($this, sprintf(
                        'Expected data with key "%s" to be an array',
                        $key
                    ));
                }

                foreach ($data as $entry) {
                    $this->assertJsonStructure($value, $entry);
                }

                continue;
            }

            if (is_array($value)) {
                if (!array_key_exists($data, $key)) {
                    throw new R\ContentException($this, sprintf(
                        'Expected data to have key "%s"',
                        $key
                    ));
                }

                $this->assertJsonStructure($structure[$key], $data[$key]);

                continue;
            }

            PHPUnit::assertArrayHasKey($value, $data);
        }

        return $this;
    }
}
