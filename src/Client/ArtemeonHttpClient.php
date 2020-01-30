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

use Artemeon\HttpClient\Client\Options\ClientOptions;
use Artemeon\HttpClient\Client\Options\ClientOptionsConverter;
use Artemeon\HttpClient\Exception\HttpClientException;
use Artemeon\HttpClient\Exception\Request\Http\ClientResponseException;
use Artemeon\HttpClient\Exception\Request\Http\RedirectResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ResponseException;
use Artemeon\HttpClient\Exception\Request\Http\ServerResponseException;
use Artemeon\HttpClient\Exception\Request\Network\ConnectException;
use Artemeon\HttpClient\Exception\Request\TransferException;
use Artemeon\HttpClient\Http\Header\Fields\UserAgent;
use Artemeon\HttpClient\Http\Header\Header;
use Artemeon\HttpClient\Http\Header\HeaderField;
use Artemeon\HttpClient\Http\Header\Headers;
use Artemeon\HttpClient\Http\Request;
use Artemeon\HttpClient\Http\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Exception\TooManyRedirectsException as GuzzleTooManyRedirectsException;
use GuzzleHttp\Exception\TransferException as GuzzleTransferException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as GuzzleResponse;
use RuntimeException;

/**
 * HttpClient implementation with guzzle
 */
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

        // Add Artemeon default user agent
        if (!$request->hasHeader(HeaderField::USER_AGENT)) {
            $userAgent = UserAgent::fromString();
            $request = $request->withHeader($userAgent->getName(), [$userAgent->getValue()]);
        }

        return $this->doSend($request, $guzzleOptions);
    }

    /**
     * Send request and transform Guzzle exception to Artemeon exceptions
     * Map Guzzle exceptions -> HttpClient exceptions:
     *
     * ```
     *  1. RuntimeException -> HttpClientException)
     *      1. SeekException -> HttpClientException
     *      2. TransferException -> TransferException
     *          1. RequestException ->  ResponseException
     *              1. BadResponseException -> ResponseException
     *                  1. ServerException -> ServerResponseException
     *                  2. ClientException -> ClientResponseException
     *              2. ConnectException -> ConnectException
     *              3. TooManyRedirectsException -> RedirectResponseException
     * ```
     *
     * @param Request $request
     * @param array $guzzleOptions
     * @throws HttpClientException
     */
    private function doSend(Request $request, array $guzzleOptions): Response
    {
        $guzzleRequest = $this->convertToGuzzleRequest($request);

        try {
            $response = $this->guzzleClient->send($guzzleRequest, $guzzleOptions);
        } catch (GuzzleClientException $previous) {
            // (guzzle) ClientException -> ClientResponseException
            $response = $this->convertFromGuzzleResponse($previous->getResponse());
            throw ClientResponseException::fromResponse($response, $request, $previous->getMessage(), $previous);
        } catch (GuzzleServerException $previous) {
            // (guzzle) ServerException -> ServerResponseException
            $response = $this->convertFromGuzzleResponse($previous->getResponse());
            throw ServerResponseException::fromResponse($response, $request, $previous->getMessage(), $previous);
        } catch (GuzzleBadResponseException $previous) {
            // (guzzle) BadResponseException -> ResponseException
            $response = $this->convertFromGuzzleResponse($previous->getResponse());
            throw ResponseException::fromResponse($response, $request, $previous->getMessage(), $previous);
        } catch (GuzzleConnectException $previous) {
            // (guzzle) ConnectException -> ConnectException
            throw ConnectException::fromRequest($request, $previous->getMessage(), $previous);
        } catch (GuzzleTooManyRedirectsException $previous) {
            $response = $this->convertFromGuzzleResponse($previous->getResponse());
            throw RedirectResponseException::fromResponse($response, $request, $previous->getMessage(), $previous);
        } catch (GuzzleRequestException  $previous) {
            // (guzzle) RequestException ->  ResponseException
            $response = $this->convertFromGuzzleResponse($previous->getResponse());
            throw ResponseException::fromResponse($response, $request, $previous->getMessage(), $previous);
        } catch (GuzzleTransferException $previous) {
            // (guzzle) TransferException -> TransferException
            throw TransferException::fromRequest($request, $previous->getMessage(), $previous);
        } catch (RuntimeException $previous) {
            throw HttpClientException::fromGuzzleException($previous);
        }

        return $this->convertFromGuzzleResponse($response);
    }

    /**
     * Converts our Request object to a GuzzleRequest
     *
     * @throws HttpClientException
     */
    private function convertToGuzzleRequest(Request $request): GuzzleRequest
    {
        try {
            return new GuzzleRequest(
                $request->getMethod(),
                $request->getUrl(),
                $request->getHeaders(),
                $request->getBody(),
                $request->getProtocolVersion()
            );
        } catch (InvalidArgumentException $exception) {
            throw HttpClientException::fromGuzzleException($exception);
        }
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
            $headers->addHeader(Header::fromArray($headerField, $guzzleResponse->getHeader($headerField)));
        }

        return new Response(
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getProtocolVersion(),
            $guzzleResponse->getBody(),
            $headers
        );
    }
}
