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
use Artemeon\HttpClient\Exception\RuntimeException;
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
use GuzzleHttp\Exception\SeekException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Exception\TooManyRedirectsException as GuzzleTooManyRedirectsException;
use GuzzleHttp\Exception\TransferException as GuzzleTransferException;
use Psr\Http\Message\ResponseInterface as GuzzleResponse;

/**
 * HttpClient implementation with guzzle
 */
class ArtemeonHttpClient implements HttpClient
{
    private GuzzleClient $guzzleClient;
    private ClientOptionsConverter $clientOptionsConverter;

    public function __construct(GuzzleClient $guzzleClient, ClientOptionsConverter $clientOptionsConverter)
    {
        $this->guzzleClient = $guzzleClient;
        $this->clientOptionsConverter = $clientOptionsConverter;
    }

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
     * ```
     *  1. \RuntimeException -> RuntimeException (HttpClientException)
     *      1. SeekException
     *      2. GuzzleTransferException -> TransferException
     *          1. GuzzleRequestException ->  ResponseException
     *              1. GuzzleConnectException -> ConnectException
     *              2. GuzzleTooManyRedirectsException -> RedirectResponseException
     *              3. GuzzleBadResponseException -> ResponseException
     *                  1. GuzzleServerException -> ServerResponseException
     *                  2. GuzzleClientException -> ClientResponseException
     * ```
     *
     * @throws HttpClientException
     */
    private function doSend(Request $request, array $guzzleOptions): Response
    {
        try {
            $response = $this->guzzleClient->send($request, $guzzleOptions);
        } catch (GuzzleClientException $previous) {
            throw ClientResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleServerException $previous) {
            throw ServerResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleBadResponseException $previous) {
            throw ResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleTooManyRedirectsException $previous) {
            throw RedirectResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleConnectException $previous) {
            throw ConnectException::fromRequest($request, $previous->getMessage(), $previous);
        } catch (GuzzleRequestException $previous) {
            throw ResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleTransferException $previous) {
            throw TransferException::fromRequest($request, $previous->getMessage(), $previous);
        } catch (SeekException $previous) {
            throw RuntimeException::fromGuzzleException($previous);
        } catch (\RuntimeException $previous) {
            throw RuntimeException::fromGuzzleException($previous);
        }

        return $this->convertGuzzleResponse($response);
    }

    /**
     * Checks the Guzzle exception for a response object and converts it to a Artemeon response object
     */
    private function getResponseFromGuzzleException(GuzzleRequestException $guzzleRequestException): ?Response
    {
        if (!$guzzleRequestException->hasResponse()) {
            return null;
        }

        return $this->convertGuzzleResponse($guzzleRequestException->getResponse());
    }

    /**
     * Converts a GuzzleResponse object to our Response object
     */
    private function convertGuzzleResponse(GuzzleResponse $guzzleResponse): Response
    {
        $headers = Headers::create();

        foreach (array_keys($guzzleResponse->getHeaders()) as $headerField) {
            $headers->add(Header::fromArray($headerField, $guzzleResponse->getHeader($headerField)));
        }

        return new Response(
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getProtocolVersion(),
            $guzzleResponse->getBody(),
            $headers,
            $guzzleResponse->getReasonPhrase()
        );
    }
}
