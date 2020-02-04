<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Uri;
use Http\Psr7Test\UriIntegrationTest;

class UriTest extends UriIntegrationTest
{

    /**
     * @inheritDoc
     */
    public function createUri($uri)
    {
        return Uri::fromString($uri);
    }
}
