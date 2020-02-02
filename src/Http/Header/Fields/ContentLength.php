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
 * Class to describe the header field 'Content-Length'
 */
class ContentLength implements HeaderField
{
    /** @var int */
    private $contentLength;

    /**
     * ContentLength constructor.
     *
     * @param int $contentLength
     */
    public function __construct(int $contentLength)
    {
        $this->contentLength = $contentLength;
    }

    /**
     * Named constructor to create an instance from the given int value
     *
     * @param int $contentLength
     */
    public static function fromInt(int $contentLength): self
    {
        return new self($contentLength);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return HeaderField::CONTENT_LENGTH;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return strval($this->contentLength);
    }
}
