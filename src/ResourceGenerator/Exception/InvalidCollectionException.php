<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator\Exception;

use RuntimeException;

class InvalidCollectionException extends RuntimeException implements Exception
{
    /**
     * @param mixed $instance The invalid collection instance or value.
     */
    public static function fromInstance($instance, string $class) : self
    {
        return new self(sprintf(
            '%s is unable to create a resource for collection of type "%s"; not a Traversable',
            $class,
            is_object($instance) ? get_class($instance) : gettype($instance)
        ));
    }
}
