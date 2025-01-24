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

/**
 * Class to describe the header field 'Content-Length'.
 */
class ContentLength implements HeaderField
{
    public function __construct(private readonly int $contentLength)
    {
    }

    /**
     * Named constructor to create an instance from the given int value.
     */
    public static function fromInt(int $contentLength): self
    {
        return new self($contentLength);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return HeaderField::CONTENT_LENGTH;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getValue(): string
    {
        return (string) ($this->contentLength);
    }
}
