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

use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Stream\Stream;
use Override;
use Psr\Http\Message\StreamInterface;

/**
 * Encoder for "application/x-www-form-urlencoded" encoded body content.
 */
class FormUrlEncoder implements Encoder
{
    /**
     * FormUrlEncoder constructor.
     *
     * @param array $formValues Array with the form values to encode: ['formFieldName' = 'value'],
     */
    private function __construct(private readonly array $formValues)
    {
    }

    /**
     * Named constructor to create an instance based on the given array.
     *
     * ```php
     * $encoder = FormUrlEncoder->fromArray(['username' = 'John.Doe'])
     * $encoder->encode();
     * ```
     *
     * @param array $formValues ['formFieldName' = 'value'],
     */
    public static function fromArray(array $formValues): self
    {
        return new self($formValues);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function encode(): StreamInterface
    {
        return Stream::fromString(http_build_query($this->formValues));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMimeType(): string
    {
        return MediaType::FORM_URL_ENCODED;
    }
}
