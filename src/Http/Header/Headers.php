<?php

/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Artemeon\HttpClient\Http\Header;

use ArrayIterator;
use Artemeon\HttpClient\Exception\HttpClientException;
use Countable;
use IteratorAggregate;

/**
 * Header collection class for http requests and responses
 */
class Headers implements Countable, IteratorAggregate
{
    /** @var Header[] */
    private $headers;

    /**
     * Headers constructor.
     */
    private function __construct()
    {
        $this->headers = [];
    }

    /**
     * Named constructor to create an instance based on the given array of HeaderField objects
     *
     * @param HeaderField[] $headerFields
     * @throws HttpClientException
     */
    public static function fromFields(array $headerFields): self
    {
        $instance = new self();

        foreach ($headerFields as $field) {
            $instance->addHeader(Header::fromField($field));
        }

        return $instance;
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * Adds a header to the collection, throws an exception if the header already exists
     *
     * @throws HttpClientException
     */
    public function addHeader(Header $header): void
    {
        $fieldName = $header->getFieldName();

        if ($this->hasHeader($fieldName)) {
            throw HttpClientException::forAlreadyRegisteredHeaderFields($fieldName);
        }

        // A user agent SHOULD generate Host as the first header field
        if (strtolower($fieldName) === strtolower(HeaderField::HOST)) {
            $this->headers = [$fieldName => $header] + $this->headers;
        } else {
            $this->headers[$fieldName] = $header;
        }
    }

    /**
     * Adds a header to the collection or replaces an already existing header
     */
    public function replaceHeader(Header $header): void
    {
        $fieldName = $header->getFieldName();

        // A user agent SHOULD generate Host as the first header field
        if (strtolower($fieldName) === strtolower(HeaderField::HOST) && !isset($this->headers[$fieldName])) {
            $this->headers = [$fieldName => $header] + $this->headers;
        } else {
            $this->headers[$fieldName] = $header;
        }
    }

    /**
     * Checks case incentive for a specific header field
     */
    public function hasHeader(string $headerField): bool
    {
        foreach ($this->headers as $header) {
            if (strtolower($headerField) === strtolower($header->getFieldName())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a Header object for the given header field name
     *
     * @throws HttpClientException
     */
    public function getHeader($headerField): Header
    {
        if (!$this->hasHeader($headerField)) {
            throw HttpClientException::forNonExistentHeaderFields($headerField);
        }

        return $this->headers[$headerField];
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->headers);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->headers);
    }
}
