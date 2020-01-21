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

namespace Artemeon\HttpClient\Client;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Http\Message\ResponseInterface as GuzzleResponse;

class ArtemeonHttpClient implements HttpClient
{
    /** @var GuzzleClient */
    private $guzzleClient;

    /** @var ClientOptionsConverter */
    private $clientOptionsConverter;

    /**
     * GuzzleHttpClient constructor.
     */
    public function __construct(GuzzleClient $guzzleClient, ClientOptionsConverter $clientOptionsConverter)
    {
        $this->guzzleClient = $guzzleClient;
        $this->clientOptionsConverter = $clientOptionsConverter;
    }

    /**
     * @inheritDoc
     * @throws HttpClientException
     */
    final public function send(Request $request, ClientOptions $clientOptions = null): Response
    {
        if ($clientOptions instanceof ClientOptions) {
            $guzzleOptions = $this->clientOptionsConverter->toGuzzleOptionsArray($clientOptions);
        } else {
            $guzzleOptions = [];
        }

        $guzzleRequest = $this->convertToGuzzleRequest($request);
        $guzzleResponse = $this->guzzleClient->send($guzzleRequest, $guzzleOptions);

        return $this->convertFromGuzzleResponse($guzzleResponse);
    }

    /**
     * Converts our Request object to a GuzzleRequest
     *
     * @throws HttpClientException
     */
    private function convertToGuzzleRequest(Request $request): GuzzleRequest
    {
        return new GuzzleRequest(
            $request->getMethod(),
            $request->getUrl(),
            $request->getHeaders(),
            $request->getBody(),
            $request->getProtocolVersion()
        );
    }

    /**
     * Converts a GuzzleResponse object to our Response object
     *
     * @throws HttpClientException
     */
    private function convertFromGuzzleResponse(GuzzleResponse $guzzleResponse): Response
    {
        $headers = Headers::create();

        foreach (array_keys($guzzleResponse->getHeaders()) as $headerField) {
            $headers->addHeader(Header::fromString($headerField, $guzzleResponse->getHeader($headerField)));
        }

        return new Response(
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getProtocolVersion(),
            $guzzleResponse->getBody(),
            $headers
        );
    }
}
