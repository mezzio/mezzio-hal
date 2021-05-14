<?php

namespace MezzioTest\Hal\TestAsset;

class Uri
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
