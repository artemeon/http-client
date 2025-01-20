<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Uri;
use Http\Psr7Test\UriIntegrationTest;

/**
 * @covers \Artemeon\HttpClient\Http\Uri
 */
class UriTest extends UriIntegrationTest
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function createUri($uri)
    {
        return Uri::fromString($uri);
    }
}
