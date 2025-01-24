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

namespace Artemeon\HttpClient\Tests\Unit\Http\Body\Encoder;

use Artemeon\HttpClient\Http\Body\Encoder\FormUrlEncoder;
use Artemeon\HttpClient\Http\MediaType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function http_build_query;

/**
 * @internal
 */
#[CoversClass(FormUrlEncoder::class)]
class FormUrlEncoderTest extends TestCase
{
    #[Test]
    public function testEncodeReturnsExpectedString(): void
    {
        $values = ['user' => 'Ernst Müller'];
        $encoder = FormUrlEncoder::fromArray($values);

        self::assertSame(http_build_query($values), $encoder->encode()->__toString());
        self::assertSame(MediaType::FORM_URL_ENCODED, $encoder->getMimeType());
    }
}
