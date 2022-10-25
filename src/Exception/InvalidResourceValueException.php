<?php

declare(strict_types=1);

namespace Mezzio\Hal\Exception;

use Mezzio\Hal\HalResource;
use RuntimeException;

use function gettype;
use function is_object;
use function sprintf;

class InvalidResourceValueException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $value
     */
    public static function fromValue($value): self
    {
        return new self(sprintf(
            'Encountered non-primitive type "%s" when serializing %s instance; unable to serialize',
            is_object($value) ? $value::class : gettype($value),
            HalResource::class
        ));
    }

    /**
     * @param object $object
     */
    public static function fromObject($object): self
    {
        return new self(sprintf(
            'Encountered object of type "%s" when serializing %s instance; unable to serialize',
            $object::class,
            HalResource::class
        ));
    }
}
