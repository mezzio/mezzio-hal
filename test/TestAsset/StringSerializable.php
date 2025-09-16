<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

use Stringable;

final class StringSerializable implements Stringable
{
    public function __toString(): string
    {
        return __METHOD__;
    }
}
