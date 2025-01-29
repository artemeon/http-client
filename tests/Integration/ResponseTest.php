<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Response;
use Artemeon\HttpClient\Tests\TestCase;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use Throwable;
use TypeError;

/**
 * @covers \Artemeon\HttpClient\Http\Response
 *
 * @internal
 */
class ResponseTest extends TestCase
{
    private Response $response;

    /**
     * Overwrite, parent code doesn't work witz Guzzle > 7.2, remove when paren code is fixed.
     */
    protected function buildStream($data)
    {
        return Utils::streamFor($data);
    }

    /**
     * {@inheritDoc}
     */
    public function createSubject()
    {
        return new Response(200, '1.1');
    }

    protected function setUp(): void
    {
        $this->response = $this->createSubject();
    }

    protected function getMessage()
    {
        return $this->response;
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

    /**
     * @dataProvider getInvalidStatusCodeArguments
     */
    public function testStatusCodeInvalidArgument($statusCode): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        try {
            $this->response->withStatus($statusCode);
            $this->fail('withStatus() should have raised exception on invalid argument');
        } catch (AssertionFailedError $e) {
            // invalid argument not caught
            throw $e;
        } catch (InvalidArgumentException | TypeError $e) {
            // valid
            $this->assertInstanceOf(Throwable::class, $e);
        } catch (Throwable $e) {
            // invalid
            $this->fail(sprintf(
                'Unexpected exception (%s) thrown from withStatus(); expected TypeError or InvalidArgumentException',
                gettype($e),
            ));
        }
    }

    public static function getInvalidStatusCodeArguments()
    {
        return [
            'true' => [true],
            'string' => ['foobar'],
            'too-low' => [99],
            'too-high' => [600],
            'object' => [new \stdClass()],
        ];
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
