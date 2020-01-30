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
use Artemeon\HttpClient\Stream\AppendableStream;
use Artemeon\HttpClient\Stream\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Encoder for "multipart/form-data" encoded body content
 */
class MultipartFormDataEncoder implements Encoder
{
    /** @var string */
    private $boundary;

    /** @var AppendableStream */
    private $multiPartStream;

    /** @var string */
    private $crlf = "\r\n";

    /**
     * MultipartFormDataEncoder constructor.
     *
     * @param string $boundary Boundary string 7bit US-ASCII
     * @throws HttpClientException
     */
    private function __construct(string $boundary)
    {
        $this->boundary = trim($boundary);
        $this->multiPartStream = Stream::fromFileMode('r+');
    }

    /**
     * Named constructor to create an instance
     *
     * @throws HttpClientException
     */
    public static function create(): self
    {
        $boundary = uniqid('');
        return new self($boundary);
    }

    /**
     * Add a new multipart section for form fields
     *
     * @param string $fieldName Name of the form field
     * @param string $value Value of the form field
     * @throws HttpClientException
     */
    public function addFieldPart(string $fieldName, string $value): self
    {
        $encoding = $this->detectEncoding($value);

        $part = '--' . $this->boundary . $this->crlf;
        $part .= sprintf('Content-Disposition: form-data; name="%s"', $fieldName) . $this->crlf;
        $part .= sprintf('Content-Type: text/plain; charset=%s', $encoding) . $this->crlf;
        $part .= $this->crlf;
        $part .= $value . $this->crlf;

        try {
            $this->multiPartStream->appendStream(Stream::fromString($part));
        } catch (RuntimeException $exception) {
            throw HttpClientException::fromGuzzleException($exception);
        }

        return $this;
    }

    /**
     * Add a new multipart section for file upload fields
     *
     * @param string $name Name of the form field
     * @param string $fileName Name of the file, with a valid file extension
     * @param AppendableStream $fileContent Binary stream of the file
     * @throws HttpClientException
     */
    public function addFilePart(string $name, string $fileName, AppendableStream $fileContent): self
    {
        $fileExtension = preg_replace('/^.*\.([^.]+)$/', '$1', $fileName);

        $part = '--' . $this->boundary . $this->crlf;
        $part .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"', $name, $fileName) . $this->crlf;
        $part .= sprintf('Content-Type: %s', MediaType::mapFileExtensionToMimeType($fileExtension)) . $this->crlf;
        $part .= $this->crlf;

        try {
            $this->multiPartStream->appendStream(Stream::fromString($part));
            $this->multiPartStream->appendStream($fileContent);
            $this->multiPartStream->appendStream(Stream::fromString($this->crlf));
        } catch (RuntimeException $exception) {
            throw HttpClientException::fromGuzzleException($exception);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function encode(): StreamInterface
    {
        try {
            $this->multiPartStream->appendStream(Stream::fromString('--' . $this->boundary . '--' . $this->crlf));
        } catch (RuntimeException $exception) {
            throw HttpClientException::fromGuzzleException($exception);
        }

        return $this->multiPartStream;
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(): string
    {
        return sprintf('%s; boundary="%s"', MediaType::MULTIPART_FORM_DATA, $this->boundary);
    }

    /**
     * Detects the encoding of the given string
     *
     * @throws HttpClientException
     */
    private function detectEncoding(string $value): string
    {
        $encoding = mb_detect_encoding($value);

        if ($encoding === false) {
            throw new HttpClientException("Cant't detect encoding for multipart");
        }

        return $encoding;
    }
}
