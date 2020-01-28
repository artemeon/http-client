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

namespace Artemeon\HttpClient\Http\Body\Encoder;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Encoder for "application/json" encoded body content
 */
class JsonEncoder implements Encoder
{
    /** @var array|object */
    private $value;

    /** @var int */
    private $options;

    /**
     * JsonEncoder constructor.
     *
     * @param mixed $value String, object or array to encode
     * @param int $options Json encode options: @see https://www.php.net/manual/de/function.json-encode.php
     */
    private function __construct($value, int $options = 0)
    {
        $this->value = $value;
        $this->options = $options;
    }

    /**
     * Named constructor to create an instance based on the given array
     *
     * ```php
     * # Associative arrays are always encoded as json object:
     * $encoder = JsonEncoder::fromArray(['username' = 'John.Doe'])
     *
     * # Use JSON options: @see https://www.php.net/manual/en/function.json-encode.php
     * $encoder = JsonEncoder::fromArray(
     *     ['value1', 'value2','value3'],
     *     JSON_OBJECT_AS_ARRAY  | JSON_UNESCAPED_UNICODE
     * )
     *
     * $encoder->encode();
     * ```
     *
     * @param array $value Array to encode, associative array always encoded as json object
     * @param bool $options Bitmask of json constants: @see https://www.php.net/manual/en/function.json-encode.php
     */
    public static function fromArray(array $value, int $options = 0): self
    {
        return new self($value, $options);
    }

    /**
     * Named constructor to create an instance based on the given object
     *
     * @param object $value Object to encode
     * @param int $options Bitmask of json constants:
     * @see https://www.php.net/manual/en/function.json-encode.php
     */
    public static function fromObject(object $value, int $options = 0): self
    {
        return new self($value, $options);
    }

    /**
     * @inheritDoc
     * @throws HttpClientException
     */
    public function encode(): StreamInterface
    {
        $json = json_encode($this->value, $this->options);

        if ($json === false) {
            $error = json_last_error_msg();
            throw new HttpClientException("Can't encode to json: $error");
        }

        return Stream::fromString($json);
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(): string
    {
        return MediaType::JSON;
    }
}
