<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use Http\Psr7Test\RequestIntegrationTest;

/**
 * @covers \Artemeon\HttpClient\Http\Request
 */
class RequestTest extends RequestIntegrationTest
{
    /**
     * @inheritDoc
     */
    public function createSubject()
    {
        return Request::forGet(Uri::fromString('/'));
    }
}
