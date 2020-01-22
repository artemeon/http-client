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
use Artemeon\HttpClient\Http\Body\Reader\Reader;
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
        // json_encode needs UTF-8 encoded data
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = utf8_encode($value);
        }

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
     * # Use second parameter to force json object even for non associative arrays
     * $encoder = JsonEncoder::fromArray(['value1', 'value2','value3'], true)
     * $encoder->encode();
     * ```
     *
     * @param array $value Array to encode, associative array always encoded as json object
     * @param bool $forceObject Set to true to force non-associative arrays encoded as json object
     */
    public static function fromArray(array $value, bool $forceObject = false): self
    {
        $options = $forceObject === true ? JSON_FORCE_OBJECT : 0;
        return new self($value, $options);
    }

    /**
     * Named constructor to create an instance based on the given object
     *
     * @param object $value Object to encode
     */
    public static function fromObject(object $value): self
    {
        return new self($value, 0);
    }

    /**
     * Named constructor to create an instance based on the given string
     *
     * @param string $value String to encode
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Named constructor to create an instance based on the given reader
     *
     * @param Reader $reader
     */
    public static function fromReader(Reader $reader)
    {
        return new self($reader->getStream()->getContents());
    }

    /**
     * @inheritDoc
     * @throws HttpClientException
     */
    public function encode(): StreamInterface
    {
        $json = json_encode($this->value);

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