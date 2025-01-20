<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Tests\Integration;

use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Uri;
use GuzzleHttp\Psr7\Utils;
use Http\Psr7Test\RequestIntegrationTest;

/**
 * @covers \Artemeon\HttpClient\Http\Request
 */
class RequestTest extends RequestIntegrationTest
{
    /**
     * Overwrite, parent code doesn't work witz Guzzle > 7.2, remove when paren code is fixed
     */
    #[\Override]
    protected function buildStream($data)
    {
        return Utils::streamFor($data);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function createSubject()
    {
        $this->skippedTests['testMethodIsExtendable'] = "";
        return Request::forGet(Uri::fromString('/'));
    }
}
