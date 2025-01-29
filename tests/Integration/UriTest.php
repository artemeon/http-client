<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Uri;
use Artemeon\HttpClient\Tests\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Artemeon\HttpClient\Http\Uri
 *
 * @internal
 */
class UriTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function createUri($uri)
    {
        return Uri::fromString($uri);
    }

    /**
     * Tests that getPath() normalizes multiple leading slashes to a single
     * slash. This is done to ensure that when a path is used in isolation from
     * the authority, it will not cause URL poisoning and/or XSS issues.
     *
     * @see https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2015-3257
     *
     * @psalm-param array{expected: non-empty-string, uri: UriInterface} $test
     */
    public function testGetPathNormalizesMultipleLeadingSlashesToSingleSlashToPreventXSS()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $expected = 'http://example.org//valid///path';
        $uri = $this->createUri($expected);

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('/valid///path', $uri->getPath());

        return [
            'expected' => $expected,
            'uri' => $uri,
        ];
    }

    /**
     * Tests that the full string representation of a URI that includes multiple
     * leading slashes in the path is presented verbatim (in contrast to what is
     * provided when calling getPath()).
     *
     * @depends testGetPathNormalizesMultipleLeadingSlashesToSingleSlashToPreventXSS
     *
     * @psalm-param array{expected: non-empty-string, uri: UriInterface} $test
     */
    public function testStringRepresentationWithMultipleSlashes(array $test)
    {
        $this->assertSame($test['expected'], (string) $test['uri']);
    }

    /**
     * Tests that special chars in `userInfo` must always be URL-encoded to pass RFC3986 compliant URIs where characters
     * in username and password MUST NOT contain reserved characters.
     *
     * This test is taken from {@see https://github.com/guzzle/psr7/blob/3cf1b6d4f0c820a2cf8bcaec39fc698f3443b5cf/tests/UriTest.php#L679-L688 guzzlehttp/psr7}.
     *
     * @see https://www.rfc-editor.org/rfc/rfc3986#appendix-A
     */
    public function testSpecialCharsInUserInfo(): void
    {
        $uri = $this->createUri('/')->withUserInfo('foo@bar.com', 'pass#word');
        self::assertSame('foo%40bar.com:pass%23word', $uri->getUserInfo());
    }

    /**
     * Tests that userinfo which is already encoded is not encoded twice.
     * This test is taken from {@see https://github.com/guzzle/psr7/blob/3cf1b6d4f0c820a2cf8bcaec39fc698f3443b5cf/tests/UriTest.php#L679-L688 guzzlehttp/psr7}.
     */
    public function testAlreadyEncodedUserInfo(): void
    {
        $uri = $this->createUri('/')->withUserInfo('foo%40bar.com', 'pass%23word');
        self::assertSame('foo%40bar.com:pass%23word', $uri->getUserInfo());
    }
}
