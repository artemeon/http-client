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

namespace Artemeon\HttpClient\Http\Header\Fields;

use Artemeon\HttpClient\Http\Header\HeaderField;
use Override;

/**
 * Class to describe the header field 'Content-Type'.
 */
class ContentType implements HeaderField
{
    /**
     * @param string $mimeType Mime type string
     */
    private function __construct(private readonly string $mimeType)
    {
    }

    /**
     * Named constructor to create an instance from the given string value.
     *
     * @param string $mimeType MIME type string @see \Artemeon\HttpClient\Http\MediaType
     */
    public static function fromString(string $mimeType): self
    {
        return new self($mimeType);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getName(): string
    {
        return HeaderField::CONTENT_TYPE;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValue(): string
    {
        return $this->mimeType;
    }
}
