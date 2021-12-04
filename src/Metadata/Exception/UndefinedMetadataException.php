<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata\Exception;

use RuntimeException;

use function sprintf;

class UndefinedMetadataException extends RuntimeException implements ExceptionInterface
{
    public static function create(string $class): self
    {
        return new self(sprintf(
            'Unable to retrieve metadata for "%s"; no matching metadata found',
            $class
        ));
    }
}
