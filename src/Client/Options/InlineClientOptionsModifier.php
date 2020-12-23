<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Client\Options;

use Closure;

final class InlineClientOptionsModifier implements ClientOptionsModifier
{
    /** @var Closure */
    private $callback;

    private function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public static function fromClosure(Closure $closure): self
    {
        return new self($closure);
    }

    public static function fromCallable(callable $callable): self
    {
        return new self(Closure::fromCallable($callable));
    }

    public function modify(ClientOptions $clientOptions): ClientOptions
    {
        $callback = $this->callback;

        return $callback(clone $clientOptions);
    }
}
