<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Tests\TestCase;
use GuzzleHttp\Psr7\Utils;

/**
 * @covers \Artemeon\HttpClient\Http\Response
 */
class ResponseTest extends TestCase
{
    private Response $response;

    protected function setUp(): void
    {
        $this->response = $this->createSubject();
    }

    protected function getMessage()
    {
        return $this->response;
    }

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

    public function testStatusCode(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $original = clone $this->response;
        $response = $this->response->withStatus(204);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals($this->response, $original, 'Response MUST not be mutated');
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testReasonPhrase(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $response = $this->response->withStatus(204, 'Foobar');
        $this->assertSame(204, $response->getStatusCode());
        $this->assertEquals('Foobar', $response->getReasonPhrase());
    }
}
