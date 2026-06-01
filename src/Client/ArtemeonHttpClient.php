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
use Artemeon\HttpClient\Http\Header\HeaderField;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Exception\TooManyRedirectsException as GuzzleTooManyRedirectsException;
use GuzzleHttp\Exception\TransferException as GuzzleTransferException;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HttpClient implementation with guzzle.
 */
class ArtemeonHttpClient implements HttpClient
{
    public function __construct(
        private readonly GuzzleClient $guzzleClient,
        private readonly ClientOptionsConverter $clientOptionsConverter,
    ) {
    }

    #[Override]
    final public function send(RequestInterface $request, ?ClientOptions $clientOptions = null): ResponseInterface
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

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->doSend($request, []);
    }

    /**
     * Send request and transform Guzzle exception to Artemeon exceptions
     * Map Guzzle exceptions -> HttpClient exceptions:
     *
     * ```
     *  1. \RuntimeException -> RuntimeException (HttpClientException)
     *  -- 1. GuzzleTransferException -> TransferException
     *  ---- 1. GuzzleConnectException -> ConnectException
     *  ---- 2. GuzzleRequestException ->  ResponseException
     *  ------ 1. GuzzleTooManyRedirectsException -> RedirectResponseException
     *  ------ 2. GuzzleBadResponseException -> ResponseException
     *  -------- 1. GuzzleServerException -> ServerResponseException
     *  -------- 2. GuzzleClientException -> ClientResponseException
     * ```
     *
     * @param array<array-key, mixed> $guzzleOptions
     *
     * @throws HttpClientException
     */
    private function doSend(RequestInterface $request, array $guzzleOptions): ResponseInterface
    {
        try {
            return $this->guzzleClient->send($request, $guzzleOptions);
        } catch (GuzzleClientException $previous) {
            throw ClientResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleServerException $previous) {
            throw ServerResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleBadResponseException $previous) {
            throw ResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleTooManyRedirectsException $previous) {
            throw RedirectResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleRequestException $previous) {
            throw ResponseException::fromResponse($this->getResponseFromGuzzleException($previous), $request, $previous->getMessage(), $previous);
        } catch (GuzzleConnectException $previous) {
            throw ConnectException::fromRequest($request, $previous->getMessage(), $previous);
        } catch (GuzzleTransferException $previous) {
            throw TransferException::fromRequest($request, $previous->getMessage(), $previous);
        } catch (\RuntimeException $previous) {
            throw RuntimeException::fromPreviousException($previous);
        } catch (GuzzleException $previous) {
            throw RuntimeException::fromGuzzleException($previous);
        }
    }

    /**
     * Checks the Guzzle exception for a response object and converts it to a Artemeon response object.
     */
    private function getResponseFromGuzzleException(GuzzleRequestException $guzzleRequestException): ?ResponseInterface
    {
        if (!$guzzleRequestException->hasResponse()) {
            return null;
        }

        return $guzzleRequestException->getResponse();
    }
}
