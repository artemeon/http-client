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
use Artemeon\HttpClient\Exception\InvalidArgumentException;
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
     * @throws InvalidArgumentException
     */
    public static function fromFields(array $headerFields): self
    {
        $instance = new self();

        foreach ($headerFields as $field) {
            $instance->add(Header::fromField($field));
        }

        return $instance;
    }

    /**
     * Named constructor to create an empty collection instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Adds a header to the collection, throws an exception if the header already exists
     *
     * @param Header $header The Header to add
     * @throws InvalidArgumentException
     */
    public function add(Header $header): void
    {
        $fieldName = strtolower($header->getFieldName());

        if ($this->has($fieldName)) {
            throw InvalidArgumentException::forAlreadyRegisteredHeaderFields($fieldName);
        }

        // RFC: A user agent SHOULD generate Host as the first header field
        if ($fieldName === strtolower(HeaderField::HOST)) {
            $this->headers = [$fieldName => $header] + $this->headers;
        } else {
            $this->headers[$fieldName] = $header;
        }
    }

    /**
     * Adds a header to the collection or replaces an already existing header
     *
     * @param Header $header The header to replace
     */
    public function replace(Header $header): void
    {
        $fieldName = strtolower($header->getFieldName());

        // RFC:: A user agent SHOULD generate Host as the first header field
        if ($fieldName === strtolower(HeaderField::HOST) && !isset($this->headers[$fieldName])) {
            $this->headers = [$fieldName => $header] + $this->headers;
        } else {
            $this->headers[$fieldName] = $header;
        }
    }

    /**
     * Checks case incentive for a specific header field
     *
     * @param string $headerField The header field to check
     */
    public function has(string $headerField): bool
    {
        $headerField = strtolower($headerField);
        return isset($this->headers[$headerField]);
    }

    /**
     * Checks if the header with given headerField contains an empty value string
     *
     * @param string $headerField The header field to check
     */
    public function isEmpty(string $headerField): bool
    {
        try {
            return empty($this->get($headerField)->getValue());
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Removes the header with the given header field name
     *
     * @param string $headerField The header field to remove
     */
    public function remove(string $headerField): void
    {
        $headerField = strtolower($headerField);

        if (!$this->has($headerField)) {
            return;
        }

        unset($this->headers[$headerField]);
    }

    /**
     * Return a Header object for the given header field name
     *
     * @param string $headerField The header to get
     * @throws InvalidArgumentException
     */
    public function get(string $headerField): Header
    {
        $headerField = strtolower($headerField);

        if (!$this->has($headerField)) {
            throw InvalidArgumentException::forNonExistentHeaderFields($headerField);
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
