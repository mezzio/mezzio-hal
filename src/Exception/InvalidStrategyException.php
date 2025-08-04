<?php

declare(strict_types=1);

namespace Mezzio\Hal\Exception;

use InvalidArgumentException;
use Mezzio\Hal\ResourceGenerator\StrategyInterface;

use function get_debug_type;
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

    public static function forInstance(mixed $strategy): self
    {
        return new self(sprintf(
            'Invalid strategy of type "%s"; does not implement %s',
            get_debug_type($strategy),
            StrategyInterface::class
        ));
    }
}
