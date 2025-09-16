<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use Mezzio\Hal\ResourceGenerator;
use RuntimeException;

use function get_debug_type;
use function sprintf;

/** @final */
class InvalidConfigException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $config
     */
    public static function dueToNonArray($config): self
    {
        return new self(sprintf(
            'Invalid %s configuration; expected an array or ArrayAccess instance, but received %s',
            ResourceGenerator::class,
            get_debug_type($config)
        ));
    }

    /**
     * @param mixed $strategies
     */
    public static function dueToInvalidStrategies($strategies): self
    {
        return new self(sprintf(
            'Invalid mezzio-hal.resource-generator.strategies configuration; '
            . 'expected an array or Traversable instance, but received %s',
            get_debug_type($strategies)
        ));
    }
}
