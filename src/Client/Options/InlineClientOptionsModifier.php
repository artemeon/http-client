<?php

declare(strict_types=1);

namespace Artemeon\HttpClient\Client\Options;

use Closure;

final readonly class InlineClientOptionsModifier implements ClientOptionsModifier
{
    private function __construct(private Closure $callback)
    {
    }

    public static function fromClosure(Closure $closure): self
    {
        return new self($closure);
    }

    public static function fromCallable(callable $callable): self
    {
        return new self(Closure::fromCallable($callable));
    }

    #[\Override]
    public function modify(ClientOptions $clientOptions): ClientOptions
    {
        $callback = $this->callback;

        return $callback(clone $clientOptions);
    }
}
