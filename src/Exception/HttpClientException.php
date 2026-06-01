<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Exception;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Interface to catch all possible HttpClient exceptions.
 */
interface HttpClientException extends ClientExceptionInterface
{
}
