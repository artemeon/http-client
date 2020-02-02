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
use Psr\Http\Message\UriInterface;

/**
 * Class to describe the header field 'Host'
 */
class Host implements HeaderField
{
    /** @var string */
    private $host;

    /**
     * ContentType constructor.
     *
     * @param string $host The host string
     */
    private function __construct(string $host)
    {
        $this->host = $host;
    }

    /**
     * Named constructor to create an instance based on the given Url
     *
     * @param UriInterface $uri
     */
    public static function fromUri(UriInterface $uri): self
    {
        if ($uri->getPort() === null) {
            return new self($uri->getHost());
        }

        return new self($uri->getHost() . ':' . $uri->getPort());
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return HeaderField::HOST;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->host;
    }
}
