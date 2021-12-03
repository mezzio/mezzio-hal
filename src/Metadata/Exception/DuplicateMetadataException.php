<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata\Exception;

use DomainException;

use function sprintf;

class DuplicateMetadataException extends DomainException implements ExceptionInterface
{
    public static function create(string $class): self
    {
        return new self(sprintf(
            'Attempted to add metadata for class "%s", which already has metadata in the map',
            $class
        ));
    }
}
