<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

use Stringable;

final readonly class Uri implements Stringable
{
    public function __construct(private string $uri)
    {
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}
