<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Response;
use GuzzleHttp\Psr7\Utils;
use Http\Psr7Test\ResponseIntegrationTest;

/**
 * @covers \Artemeon\HttpClient\Http\Response
 */
class ResponseTest extends ResponseIntegrationTest
{
    /**
     * Overwrite, parent code doesn't work witz Guzzle > 7.2, remove when paren code is fixed
     */
    protected function buildStream($data)
    {
        return Utils::streamFor($data);
    }

    /**
     * @inheritDoc
     */
    public function createSubject()
    {
        return new Response(200, '1.1');
    }
}
