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
 * Class to describe the header field 'User-Agent'
 */
class UserAgent implements HeaderField
{
    /** @var string */
    private $userAgent;

    /** @var string */
    public const default = "Artemeon/Http-Client";

    /**
     * UserAgent constructor.
     */
    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Named constructor to create an instance based on the given user agent string
     *
     * @param string $userAgent User agent string
     */
    public static function fromString(string $userAgent = self::default): self
    {
        return new self($userAgent);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return HeaderField::USER_AGENT;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->userAgent;
    }
}
