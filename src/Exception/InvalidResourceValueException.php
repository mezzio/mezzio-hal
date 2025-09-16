<?php

declare(strict_types=1);

namespace Mezzio\Hal\Exception;

use Mezzio\Hal\HalResource;
use RuntimeException;

use function get_debug_type;
use function sprintf;

/** @final */
class InvalidResourceValueException extends RuntimeException implements ExceptionInterface
{
    public static function fromValue(mixed $value): self
    {
        return new self(sprintf(
            'Encountered non-primitive type "%s" when serializing %s instance; unable to serialize',
            get_debug_type($value),
            HalResource::class
        ));
    }

    public static function fromObject(object $object): self
    {
        return new self(sprintf(
            'Encountered object of type "%s" when serializing %s instance; unable to serialize',
            $object::class,
            HalResource::class
        ));
    }
}
