<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Exception;

use InvalidArgumentException;
use Mezzio\Hal\ResourceGenerator\Strategy;

class InvalidStrategyException extends InvalidArgumentException implements Exception
{
    public static function forType(string $strategy) : self
    {
        return new self(sprintf(
            'Invalid strategy "%s"; does not exist, or does not implement %s',
            $strategy,
            Strategy::class
        ));
    }

    /**
     * @param mixed $strategy
     */
    public static function forInstance($strategy) : self
    {
        return new self(sprintf(
            'Invalid strategy of type "%s"; does not implement %s',
            is_object($strategy) ? get_class($strategy) : gettype($strategy),
            Strategy::class
        ));
    }
}
