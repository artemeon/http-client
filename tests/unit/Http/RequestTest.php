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

use Artemeon\HttpClient\Http\Body\Body;
use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\MediaType;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Artemeon\HttpClient\Http\Request
 * @covers \Artemeon\HttpClient\Http\Message
 */
class RequestTest extends TestCase
{
    /**
     * @test
     */
    public function forOptions_SetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forOptions(
            $expectedUrl,
            null,
            $expectedProtocol
        );

        self::assertSame(Request::METHOD_OPTIONS, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function forPost_SetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '2.0';

        $request = Request::forPost(
            $expectedUrl,
            Body::fromEncoder(FormUrlEncoder::fromArray(["username" => 'john.doe'])),
            null,
            $expectedProtocol
        );

        self::assertSame(Request::METHOD_POST, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function forPost_WillCreateAndAddContentHeader(): void
    {
        $request = Request::forPost(
            Uri::fromString('http://apache/endpoints/upload.php'),
            Body::fromEncoder(FormUrlEncoder::fromArray(["username" => 'john.doe'])),
            null // Test: Headers is null, Request must create Headers collection an add headers from body
        );

        self::assertSame(MediaType::FORM_URL_ENCODED, $request->getHeaderLine(HeaderField::CONTENT_TYPE));
        self::assertSame(17, (int) $request->getHeaderLine(HeaderField::CONTENT_LENGTH));
    }

    /**
     * @test
     */
    public function forPost_WillAddContentHeader(): void
    {
        $request = Request::forPost(
            Uri::fromString('http://apache/endpoints/upload.php'),
            Body::fromEncoder(FormUrlEncoder::fromArray(["username" => 'john.doe'])),
            Headers::fromFields([UserAgent::fromString('test')]) // Test: Add header from body to given collection
        );

        self::assertSame(MediaType::FORM_URL_ENCODED, $request->getHeaderLine(HeaderField::CONTENT_TYPE));
        self::assertSame(17, (int) $request->getHeaderLine(HeaderField::CONTENT_LENGTH));
        self::assertSame('test', $request->getHeaderLine(HeaderField::USER_AGENT));
    }

    /**
     * @test
     */
    public function forDelete_SetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forDelete(
            $expectedUrl,
            null,
            $expectedProtocol
        );

        self::assertSame(Request::METHOD_DELETE, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function forGet_SetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forGet(
            $expectedUrl,
            null,
            $expectedProtocol
        );

        self::assertSame(Request::METHOD_GET, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function forGet_UrlWillCreateAndSetHostHeader(): void
    {
        $expectedUrl = Uri::fromString('http://artemeon.de/endpoints/upload.php');
        $request = Request::forGet($expectedUrl);

        self::assertSame('artemeon.de', $request->getHeaderLine(HeaderField::HOST));
    }

    /**
     * @test
     */
    public function forGet_UrlWillAddHostHeader(): void
    {
        $expectedUrl = Uri::fromString('http://artemeon.de/endpoints/upload.php');
        $request = Request::forGet($expectedUrl, Headers::fromFields([UserAgent::fromString()]));

        self::assertSame('artemeon.de', $request->getHeaderLine(HeaderField::HOST));
    }

    /**
     * @test
     */
    public function forPut_SetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forPut(
            $expectedUrl,
            Body::fromEncoder(FormUrlEncoder::fromArray(["username" => 'john.doe'])),
            null,
            $expectedProtocol
        );

        self::assertSame(Request::METHOD_PUT, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function forPatch_SetValidRequestMethod(): void
    {
        $expectedUrl = Uri::fromString('http://apache/endpoints/upload.php');
        $expectedProtocol = '1.1';

        $request = Request::forPatch(
            $expectedUrl,
            Body::fromEncoder(FormUrlEncoder::fromArray(["username" => 'john.doe'])),
            null,
            $expectedProtocol
        );

        self::assertSame(Request::METHOD_PATCH, $request->getMethod());
        self::assertSame($expectedUrl, $request->getUri());
        self::assertSame($expectedProtocol, $request->getProtocolVersion());
    }

    /**
     * @test
     */
    public function getBody_BodyIsNull_WillReturnEmptyStreamObject(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertEmpty($request->getBody()->__toString());
    }

    /**
     * @test
     */
    public function getBody_BodyIsSet_WillReturnStreamObject(): void
    {
        $request = Request::forPost(
            Uri::fromString('http://artemeon.de/endpoints/upload.php'),
            Body::fromString(MediaType::UNKNOWN, 'test')
        );

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertSame('test', $request->getBody()->__toString());
    }

    /**
     * @test
     */
    public function hasHeader_ReturnsTrue(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertTrue($request->hasHeader(HeaderField::HOST));
    }

    /**
     * @test
     */
    public function hasHeader_ReturnsFalse(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertFalse($request->hasHeader('nit_exists'));
    }

    /**
     * @test
     */
    public function getHeader_NotExists_ReturnsEmptyArray(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertSame([], $request->getHeader('not_exists'));
    }

    /**
     * @test
     */
    public function getHeader_Exists_ReturnsValidArray(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertSame(['artemeon.de'], $request->getHeader(HeaderField::HOST));
    }

    /**
     * @test
     */
    public function getHeaderLine_NotExists_ReturnsEmptyString(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));
        self::assertSame('', $request->getHeaderLine('not_exists'));
    }

    /**
     * @test
     */
    public function getHeaderLine_Exists_ReturnsValidString(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de/endpoints/upload.php'));
        self::assertSame('www.artemeon.de', $request->getHeaderLine(HeaderField::HOST));
    }

    /**
     * @test
     */
    public function getHeaders_ReturnsValidArray(): void
    {
        $request = Request::forGet(Uri::fromString('http://artemeon.de/endpoints/upload.php'));

        self::assertCount(1, $request->getHeaders());
        self::assertArrayHasKey(HeaderField::HOST, $request->getHeaders());
        self::assertSame([HeaderField::HOST => ['artemeon.de']], $request->getHeaders());
    }

    /**
     * @test
     */
    public function getRequestTarget__WithoutPath_ReturnsSlash(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de'));
        self::assertSame('/' , $request->getRequestTarget());
    }

    /**
     * @test
     */
    public function getRequestTarget__WithPath_ReturnsPath(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de/some/Path/index.html'));
        self::assertSame('/some/Path/index.html' , $request->getRequestTarget());
    }

    /**
     * @test
     */
    public function getRequestTarget__WithPathAndQuery_ReturnsPathAnsQuery(): void
    {
        $request = Request::forGet(Uri::fromString('http://www.artemeon.de/index.html?User=john.doe'));
        self::assertSame('/index.html?User=john.doe' , $request->getRequestTarget());
    }
}
