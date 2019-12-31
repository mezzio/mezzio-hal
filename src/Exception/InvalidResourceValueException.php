<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Exception;

use Mezzio\Hal\HalResource;
use RuntimeException;

class InvalidResourceValueException extends RuntimeException implements ExceptionInterface
{
    public static function fromValue($value) : self
    {
        return new self(sprintf(
            'Encountered non-primitive type "%s" when serializing %s instance; unable to serialize',
            is_object($value) ? get_class($value) : gettype($value),
            HalResource::class
        ));
    }

    /**
     * @param object $object
     */
    public static function fromObject($object) : self
    {
        return new self(sprintf(
            'Encountered object of type "%s" when serializing %s instance; unable to serialize',
            get_class($object),
            HalResource::class
        ));
    }
}
