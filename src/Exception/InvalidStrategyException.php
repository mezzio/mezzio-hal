<?php

declare(strict_types=1);

namespace Mezzio\Hal\Exception;

use InvalidArgumentException;
use Mezzio\Hal\ResourceGenerator\StrategyInterface;

use function gettype;
use function is_object;
use function sprintf;

class InvalidStrategyException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forType(string $strategy): self
    {
        return new self(sprintf(
            'Invalid strategy "%s"; does not exist, or does not implement %s',
            $strategy,
            StrategyInterface::class
        ));
    }

    /**
     * @param mixed $strategy
     */
    public static function forInstance($strategy): self
    {
        return new self(sprintf(
            'Invalid strategy of type "%s"; does not implement %s',
            is_object($strategy) ? $strategy::class : gettype($strategy),
            StrategyInterface::class
        ));
    }
}
