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
 * Class to describe the header field 'User-Agent'.
 */
class UserAgent implements HeaderField
{
    public const string DEFAULT = 'Artemeon/HttpClient/Guzzle7';

    /**
     * @param string $userAgent The user agent string
     */
    public function __construct(private readonly string $userAgent)
    {
    }

    /**
     * Named constructor to create an instance based on the given user agent string.
     *
     * @param string $userAgent User agent string
     */
    public static function fromString(string $userAgent = self::DEFAULT): self
    {
        return new self($userAgent);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getName(): string
    {
        return HeaderField::USER_AGENT;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValue(): string
    {
        return $this->userAgent;
    }
}
