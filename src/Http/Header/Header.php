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

use function implode;

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
     */
    private function __construct(string $name, array $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * Named constructor to create an instance based on the given string values

     * @param string $name Name of the http header field
     * @param string $value Value of the http header field
     */
    public static function fromString(string $name, string $value): self
    {
        return new self($name, [$value]);
    }

    /**
     * Named constructor to create an instance based on the HeaderField object
     *
     * @param HeaderField $headerField
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
     */
    public function addValue(string $value): void
    {
        $this->values[] = $value;
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
}
