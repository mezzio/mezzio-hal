<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata\Exception;

use UnexpectedValueException;

use function sprintf;

class UndefinedClassException extends UnexpectedValueException implements ExceptionInterface
{
    public static function create(string $class): self
    {
        return new self(sprintf(
            'Cannot map metadata for class "%s"; class does not exist',
            $class
        ));
    }
}
