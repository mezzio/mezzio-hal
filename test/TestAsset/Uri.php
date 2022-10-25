<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

use Stringable;

class Uri implements Stringable
{
    /** @var string */
    private $uri;

    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}
