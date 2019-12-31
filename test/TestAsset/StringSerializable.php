<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

class StringSerializable
{
    public function __toString()
    {
        return __METHOD__;
    }
}
