<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Model\Header;

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
    public function __construct()
    {
        $this->headers = [];
    }

    /**
     * Adds a header to the collection
     * @throws HttpClientException
     */
    public function addHeader(Header $header): void
    {
        $fieldName = $header->getFieldName();

        if (isset($this->headers[$fieldName])) {
            throw HttpClientException::forAlreadyRegisteredHeaderFields($fieldName);
        }

        $this->headers[$fieldName] = $header;
    }

    /**
     * Checks for a specific header field
     */
    public function hasHeader($headerField): bool
    {
        return isset($this->headers[$headerField]);
    }

    /**
     * Return a Header object for the given header field name
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
     * Return an associative array with the header field name as a key and the header value as value
     * @return string[]
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->headers as $header) {
            $result[$header->getFieldName()] = $header->getValue();
        }

        return $result;
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
