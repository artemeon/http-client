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

namespace Artemeon\HttpClient\Tests\Http;

use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Stream\Stream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Artemeon\HttpClient\Http\Response
 */
class ResponseTest extends TestCase
{
    /**
     * @test
     */
    public function getStatusCode_ReturnValidCode()
    {
        $response = new Response(
            200,
            '1.1',
            Stream::fromString('test'),
            Headers::fromFields([UserAgent::fromString()])
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('test', $response->getBody()->__toString());
    }
}
