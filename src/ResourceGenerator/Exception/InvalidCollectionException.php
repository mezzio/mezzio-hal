<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use RuntimeException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

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
            is_object($instance) ? get_class($instance) : gettype($instance)
        ));
    }
}
