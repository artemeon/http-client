<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use Artemeon\HttpClient\Tests\TestCase;
use GuzzleHttp\Psr7\MessageTrait;
use GuzzleHttp\Psr7\Uri as GuzzleUri;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Throwable;
use TypeError;

/**
 * @covers \Artemeon\HttpClient\Http\Request
 *
 * @internal
 */
class RequestTest extends TestCase
{
    use MessageTrait;

    protected function buildUri($uri)
    {
        if (class_exists(GuzzleUri::class)) {
            return new GuzzleUri($uri);
        }

        throw new RuntimeException('Could not create URI. Check your config');
    }

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
        $this->skippedTests['testMethodIsExtendable'] = '';

        return Request::forGet(Uri::fromString('/'));
    }

    /**
     * @var array with functionName => reason
     */
    protected $skippedTests = [];

    /**
     * @var RequestInterface
     */
    protected $request;

    protected function setUp(): void
    {
        $this->request = $this->createSubject();
    }

    protected function getMessage()
    {
        return $this->request;
    }

    public function testRequestTarget(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $original = clone $this->request;
        $this->assertEquals('/', $this->request->getRequestTarget());

        $request = $this->request->withRequestTarget('*');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($this->request, $original, 'Request object MUST not be mutated');
        $this->assertEquals('*', $request->getRequestTarget());
    }

    public function testMethod(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->assertEquals('GET', $this->request->getMethod());
        $original = clone $this->request;

        $request = $this->request->withMethod('POST');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($this->request, $original, 'Request object MUST not be mutated');
        $this->assertEquals('POST', $request->getMethod());
    }

    public function testMethodIsCaseSensitive(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->request->withMethod('head');
        $this->assertEquals('head', $request->getMethod());
    }

    public function testMethodIsExtendable(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->request->withMethod('CUSTOM');
        $this->assertEquals('CUSTOM', $request->getMethod());
    }

    /**
     * @dataProvider getInvalidMethods
     */
    public function testMethodWithInvalidArguments($method): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        try {
            $this->request->withMethod($method);
            $this->fail('withMethod() should have raised exception on invalid argument');
        } catch (AssertionFailedError $e) {
            // invalid argument not caught
            throw $e;
        } catch (InvalidArgumentException | TypeError $e) {
            // valid
            $this->assertInstanceOf(Throwable::class, $e);
        } catch (Throwable $e) {
            // invalid
            $this->fail(sprintf(
                'Unexpected exception (%s) thrown from withMethod(); expected TypeError or InvalidArgumentException',
                gettype($e),
            ));
        }
    }

    public static function getInvalidMethods()
    {
        return [
            'null' => [null],
            'false' => [false],
            'array' => [['foo']],
            'object' => [new \stdClass()],
        ];
    }

    public function testUri(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        $original = clone $this->request;

        $this->assertInstanceOf(UriInterface::class, $this->request->getUri());

        $uri = $this->buildUri('http://www.foo.com/bar');
        $request = $this->request->withUri($uri);
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($this->request, $original, 'Request object MUST not be mutated');
        $this->assertEquals('www.foo.com', $request->getHeaderLine('host'));
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals('http://www.foo.com/bar', (string) $request->getUri());

        $request = $request->withUri($this->buildUri('/foobar'));
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($this->request, $original, 'Request object MUST not be mutated');
        $this->assertEquals('www.foo.com', $request->getHeaderLine('host'), 'If the URI does not contain a host component, any pre-existing Host header MUST be carried over to the returned request.');
        $this->assertEquals('/foobar', (string) $request->getUri());
    }

    public function testUriPreserveHostNoHostHost(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->request->withUri($this->buildUri('http://www.foo.com/bar'), true);
        $this->assertEquals('www.foo.com', $request->getHeaderLine('host'));
    }

    public function testUriPreserveHostNoHostNoHost(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $host = $this->request->getHeaderLine('host');
        $request = $this->request->withUri($this->buildUri('/bar'), true);
        $this->assertEquals($host, $request->getHeaderLine('host'));
    }

    public function testUriPreserveHostHostHost(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $request = $this->request->withUri($this->buildUri('http://www.foo.com/bar'));
        $host = $request->getHeaderLine('host');

        $request2 = $request->withUri($this->buildUri('http://www.bar.com/foo'), true);
        $this->assertEquals($host, $request2->getHeaderLine('host'));
    }

    /**
     * psr7-integration-tests
     * Tests that getRequestTarget(), when using the default behavior of
     * displaying the origin-form, normalizes multiple leading slashes in the
     * path to a single slash. This is done to prevent URL poisoning and/or XSS
     * issues.
     *
     * @see UriIntegrationTest::testGetPathNormalizesMultipleLeadingSlashesToSingleSlashToPreventXSS
     */
    public function testGetRequestTargetInOriginFormNormalizesUriWithMultipleLeadingSlashesInPath(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $url = 'http://example.org//valid///path';
        $request = $this->request->withUri($this->buildUri($url));
        $requestTarget = $request->getRequestTarget();

        $this->assertSame('/valid///path', $requestTarget);
    }
}
