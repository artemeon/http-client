<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Client\Options;

interface ClientOptionsModifier
{
    public function modify(ClientOptions $clientOptions): ClientOptions;
}
