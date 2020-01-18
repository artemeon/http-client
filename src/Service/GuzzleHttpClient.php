<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Service;

use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Model\ClientOptions;
use Artemeon\HttpClient\Model\Header\Header;
use Artemeon\HttpClient\Model\Header\Headers;
use Artemeon\HttpClient\Model\Request;
use Artemeon\HttpClient\Model\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Http\Message\ResponseInterface as GuzzleResponse;

use function implode;

class GuzzleHttpClient implements HttpClient
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
        if ($clientOptions instanceof  ClientOptions) {
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
     */
    private function convertToGuzzleRequest(Request $request): GuzzleRequest
    {
        return new GuzzleRequest(
            $request->getMethod(),
            $request->getUrl()->__toString(),
            $request->getHeaders()->toArray(),
            $request->hasBody() ? $request->getBody()->getContent() : null
        );
    }

    /**
     * Converts a GuzzleResponse object to our Response object
     * @throws HttpClientException
     */
    private function convertFromGuzzleResponse(GuzzleResponse $guzzleResponse): Response
    {
        $headers = new Headers();

        foreach ($guzzleResponse->getHeaders() as $headerField => $headerValue) {
            $headers->addHeader(Header::fromString($headerField, implode(' ', $headerValue)));
        }

        return new Response(
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getProtocolVersion(),
            $guzzleResponse->getBody()->getContents(),
            $headers
        );
    }
}
