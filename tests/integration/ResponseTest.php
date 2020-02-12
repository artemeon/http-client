<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Response;
use Http\Psr7Test\ResponseIntegrationTest;

/**
 * @covers \Artemeon\HttpClient\Http\Response
 */
class ResponseTest extends ResponseIntegrationTest
{
    /**
     * @inheritDoc
     */
    public function createSubject()
    {
        return new Response(200, '1.1');
    }
}
