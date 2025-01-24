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

namespace Artemeon\HttpClient\Tests\Unit\Http;

use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Http\Message;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
#[CoversClass(Request::class)]
#[CoversClass(Message::class)]
class RequestTest extends TestCase
{
    #[Test]
    public function testForOptionsSetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forOptions(
            $expectedUrl,
            null,
            $expectedProtocol,
        );

        self::assertSame(Request::METHOD_OPTIONS, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    #[Test]
    public function testForPostSetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '2.0';

        $request = Request::forPost(
            $expectedUrl,
            Body::fromEncoder(FormUrlEncoder::fromArray(['username' => 'john.doe'])),
            null,
            $expectedProtocol,
        );

        self::assertSame(Request::METHOD_POST, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    #[Test]
    public function testForPostWillCreateAndAddContentHeader(): void
    {
        $request = Request::forPost(
            Uri::fromString('http://apache/endpoints/upload.php'),
            Body::fromEncoder(FormUrlEncoder::fromArray(['username' => 'john.doe'])),
            null, // Test: Headers is null, Request must create Headers collection an add headers from body
        );

        self::assertSame(MediaType::FORM_URL_ENCODED, $request->getHeaderLine(HeaderField::CONTENT_TYPE));
        self::assertSame(17, (int) $request->getHeaderLine(HeaderField::CONTENT_LENGTH));
    }

    #[Test]
    public function testForPostWillAddContentHeader(): void
    {
        $request = Request::forPost(
            Uri::fromString('http://apache/endpoints/upload.php'),
            Body::fromEncoder(FormUrlEncoder::fromArray(['username' => 'john.doe'])),
            Headers::fromFields([UserAgent::fromString('test')]), // Test: Add header from body to given collection
        );

        self::assertSame(MediaType::FORM_URL_ENCODED, $request->getHeaderLine(HeaderField::CONTENT_TYPE));
        self::assertSame(17, (int) $request->getHeaderLine(HeaderField::CONTENT_LENGTH));
        self::assertSame('test', $request->getHeaderLine(HeaderField::USER_AGENT));
    }

    #[Test]
    public function testForDeleteSetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forDelete(
            $expectedUrl,
            null,
            $expectedProtocol,
        );

        self::assertSame(Request::METHOD_DELETE, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    #[Test]
    public function testForGetSetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forGet(
            $expectedUrl,
            null,
            $expectedProtocol,
        );

        self::assertSame(Request::METHOD_GET, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    #[Test]
    public function testForGetUrlWillCreateAndSetHostHeader(): void
    {
        $expectedUrl = Uri::fromString('http://artemeon.de/endpoints/upload.php');
        $request = Request::forGet($expectedUrl);

        self::assertSame('artemeon.de', $request->getHeaderLine(HeaderField::HOST));
    }

    #[Test]
    public function testForGetUrlWillAddHostHeader(): void
    {
        $expectedUrl = Uri::fromString('http://artemeon.de/endpoints/upload.php');
        $request = Request::forGet($expectedUrl, Headers::fromFields([UserAgent::fromString()]));

        self::assertSame('artemeon.de', $request->getHeaderLine(HeaderField::HOST));
    }

    #[Test]
    public function testForPutSetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forPut(
            $expectedUrl,
            Body::fromEncoder(FormUrlEncoder::fromArray(['username' => 'john.doe'])),
            null,
            $expectedProtocol,
        );

        self::assertSame(Request::METHOD_PUT, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    #[Test]
    public function testForPatchSetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forPatch(
            $expectedUrl,
            Body::fromEncoder(FormUrlEncoder::fromArray(['username' => 'john.doe'])),
            null,
            $expectedProtocol,
        );

        self::assertSame(Request::METHOD_PATCH, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    #[Test]
    public function testGetBodyBodyIsNullWillReturnEmptyStreamObject(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertEmpty($request->getBody()->__toString());
    }

    #[Test]
    public function testGetBodyBodyIsSetWillReturnStreamObject(): void
    {
        $request = Request::forPost(
            Uri::fromString('http://artemeon.de/endpoints/upload.php'),
            Body::fromString(MediaType::UNKNOWN, 'test'),
        );

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertSame('test', $request->getBody()->__toString());
    }

    #[Test]
    public function testHasHeaderReturnsTrue(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertTrue($request->hasHeader(HeaderField::HOST));
    }

    #[Test]
    public function testHasHeaderReturnsFalse(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertFalse($request->hasHeader('nit_exists'));
    }

    #[Test]
    public function testGetHeaderNotExistsReturnsEmptyArray(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertSame([], $request->getHeader('not_exists'));
    }

    #[Test]
    public function testGetHeaderExistsReturnsValidArray(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertSame(['artemeon.de'], $request->getHeader(HeaderField::HOST));
    }

    #[Test]
    public function testGetHeaderLineNotExistsReturnsEmptyString(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertSame('', $request->getHeaderLine('not_exists'));
    }

    #[Test]
    public function testGetHeaderLineExistsReturnsValidString(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de/endpoints/upload.php'));
        self::assertSame('www.artemeon.de', $request->getHeaderLine(HeaderField::HOST));
    }

    #[Test]
    public function testGetHeadersReturnsValidArray(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));

        self::assertCount(1, $request->getHeaders());
        self::assertArrayHasKey(HeaderField::HOST, $request->getHeaders());
        self::assertSame([HeaderField::HOST => ['artemeon.de']], $request->getHeaders());
    }

    #[Test]
    public function testGetRequestTargetWithoutPathReturnsSlash(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de'));
        self::assertSame('/', $request->getRequestTarget());
    }

    #[Test]
    public function testGetRequestTargetWithPathReturnsPath(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de/some/Path/index.html'));
        self::assertSame('/some/Path/index.html', $request->getRequestTarget());
    }

    #[Test]
    public function testGetRequestTargetWithPathAndQueryReturnsPathAnsQuery(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de/index.html?User=john.doe'));
        self::assertSame('/index.html?User=john.doe', $request->getRequestTarget());
    }
}
