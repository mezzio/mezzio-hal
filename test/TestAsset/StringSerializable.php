<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

class StringSerializable
{
    public function __toString(): string
    {
        return __METHOD__;
    }
}
