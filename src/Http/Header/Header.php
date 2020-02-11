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

use Artemeon\HttpClient\Exception\InvalidArgumentException;

/**
 * Value object for parsed http header fields
 */
class Header
{
    /** @var string */
    private $name;

    /** @var string[] */
    private $values;

    /**
     * Header constructor.
     *
     * @param string $name Name of the http header field
     * @param string[] $values Array of header values
     * @throws InvalidArgumentException
     */
    private function __construct(string $name, array $values)
    {
        $this->name = $this->assertName($name);
        $this->values = $this->assertValues($values);
    }

    /**
     * Named constructor to create an instance based on the given string value
     *
     * @param string $name Name of the http header field
     * @param string $value Value of the http header field
     * @throws InvalidArgumentException
     */
    public static function fromString(string $name, string $value): self
    {
        return new self($name, [$value]);
    }

    /**
     * Named constructor to create an instance based on the given string[] values
     *
     * @param string $name Name of the http header field
     * @param array $values Array of header values
     * @throws InvalidArgumentException
     */
    public static function fromArray(string $name, array $values)
    {
        return new self($name, $values);
    }

    /**
     * Named constructor to create an instance based on the HeaderField object
     *
     * @param HeaderField $headerField
     * @throws InvalidArgumentException
     */
    public static function fromField(HeaderField $headerField): self
    {
        return new self($headerField->getName(), [$headerField->getValue()]);
    }

    /**
     * Return the http header field name like "Accept-Encoding"
     */
    public function getFieldName(): string
    {
        return $this->name;
    }

    /**
     * Add a value to the header
     *
     * @param string $value The string value to add
     */
    public function addValue(string $value): void
    {
        $this->values[] = $value;
    }

    /**
     * Add an array of values to the header, doublets will be skipped
     *
     * @param array $values The string value to add
     */
    public function addValues(array $values): void
    {
        foreach ($values as $value) {
            // Skipp possible doublet
            if (in_array($value, $this->values)) {
                continue;
            }

            $this->values[] = $value;
        }
    }

    /**
     * Returns all value of the http header field
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Returns all values as a concatenated comma separated string
     */
    public function getValue(): string
    {
        return implode(', ', $this->values);
    }

    /**
     * Check and normalize header values
     *
     * @param array $values
     * @throws InvalidArgumentException
     */
    private function assertValues(array $values): array
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Header values can not be empty');
        }

        foreach ($values as &$value) {
            $value = trim($value);

            if ((!is_numeric($value) && !is_string($value)) || 1 !== preg_match(
                    "@^[ \t\x21-\x7E\x80-\xFF]*$@",
                    strval($value)
                )) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }
        }

        return $values;
    }

    /**
     * Check vor valid header name
     *
     * @param string $name
     * @throws InvalidArgumentException
     */
    private function assertName(string $name): string
    {
        $name = trim($name);

        if (1 !== preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $name)) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        return $name;
    }
}
