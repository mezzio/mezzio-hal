<?php

declare(strict_types=1);

namespace Mezzio\Hal\Exception;

use InvalidArgumentException;
use Mezzio\Hal\HalResource;

use function gettype;
use function sprintf;

class InvalidObjectException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $value Non-object value.
     */
    public static function forNonObject($value): self
    {
        return new self(sprintf(
            'Cannot generate %s for non-object value of type "%s"',
            HalResource::class,
            gettype($value)
        ));
    }

    public static function forUnknownType(string $class): self
    {
        return new self(sprintf(
            'Cannot generate %s for object of type %s; not in metadata map',
            HalResource::class,
            $class
        ));
    }
}
