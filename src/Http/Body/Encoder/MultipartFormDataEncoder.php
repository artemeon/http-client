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

use Artemeon\HttpClient\Exception\RuntimeException;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Stream\AppendableStream;
use Artemeon\HttpClient\Stream\Stream;
use Override;
use Psr\Http\Message\StreamInterface;

/**
 * Encoder for "multipart/form-data" encoded body content.
 */
class MultipartFormDataEncoder implements Encoder
{
    private const string CRLF = "\r\n";
    private readonly string $boundary;
    private readonly AppendableStream $multiPartStream;

    /**
     * @param string $boundary Boundary string 7bit US-ASCII
     * @throws RuntimeException
     */
    private function __construct(string $boundary)
    {
        $this->boundary = trim($boundary);
        $this->multiPartStream = Stream::fromFileMode('r+');
    }

    /**
     * Named constructor to create an instance.
     *
     * @throws RuntimeException
     */
    public static function create(): self
    {
        $boundary = uniqid('');

        return new self($boundary);
    }

    /**
     * Add a new multipart section for form fields.
     *
     * @param string $fieldName Name of the form field
     * @param string $value Value of the form field
     * @throws RuntimeException
     */
    public function addFieldPart(string $fieldName, string $value): self
    {
        $encoding = $this->detectEncoding($value);

        $part = '--' . $this->boundary . self::CRLF;
        $part .= sprintf('Content-Disposition: form-data; name="%s"', $fieldName) . self::CRLF;
        $part .= sprintf('Content-Type: text/plain; charset=%s', $encoding) . self::CRLF;
        $part .= self::CRLF;
        $part .= $value . self::CRLF;

        $this->multiPartStream->appendStream(Stream::fromString($part));

        return $this;
    }

    /**
     * Add a new multipart section for file upload fields.
     *
     * @param string $name Name of the form field
     * @param string $fileName Name of the file, with a valid file extension
     * @param AppendableStream $fileContent Binary stream of the file
     * @throws RuntimeException
     */
    public function addFilePart(string $name, string $fileName, AppendableStream $fileContent): self
    {
        $fileExtension = preg_replace('/^.*\.([^.]+)$/', '$1', $fileName);

        $part = '--' . $this->boundary . self::CRLF;
        $part .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"', $name, $fileName) . self::CRLF;
        $part .= sprintf('Content-Type: %s', MediaType::mapFileExtensionToMimeType($fileExtension)) . self::CRLF;
        $part .= self::CRLF;

        $this->multiPartStream->appendStream(Stream::fromString($part));
        $this->multiPartStream->appendStream($fileContent);
        $this->multiPartStream->appendStream(Stream::fromString(self::CRLF));

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function encode(): StreamInterface
    {
        // Add the end boundary
        $this->multiPartStream->appendStream(Stream::fromString('--' . $this->boundary . '--' . self::CRLF));

        return $this->multiPartStream;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMimeType(): string
    {
        return sprintf('%s; boundary="%s"', MediaType::MULTIPART_FORM_DATA, $this->boundary);
    }

    /**
     * Detects the encoding of the given string.
     *
     * @throws RuntimeException
     */
    private function detectEncoding(string $value): string
    {
        $encoding = mb_detect_encoding($value);

        if ($encoding === false) {
            throw new RuntimeException("Can't detect encoding for multipart");
        }

        return $encoding;
    }
}
