<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use RuntimeException;

use function get_debug_type;
use function sprintf;

/** @final */
class InvalidCollectionException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $instance The invalid collection instance or value.
     */
    public static function fromInstance($instance, string $class): self
    {
        return new self(sprintf(
            '%s is unable to create a resource for collection of type "%s"; not a Traversable',
            $class,
            get_debug_type($instance)
        ));
    }
}
