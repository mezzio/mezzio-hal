<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use Mezzio\Hal\Metadata\AbstractCollectionMetadata;
use Mezzio\Hal\Metadata\AbstractMetadata;
use RuntimeException;

use function sprintf;

class UnexpectedMetadataTypeException extends RuntimeException implements ExceptionInterface
{
    public static function forMetadata(AbstractMetadata $metadata, string $strategy, string $expected): self
    {
        return new self(sprintf(
            'Unexpected metadata of type %s was mapped to %s (expects %s)',
            $metadata::class,
            $strategy,
            $expected
        ));
    }

    public static function forCollection(AbstractMetadata $metadata, string $strategyClass): self
    {
        return new self(sprintf(
            'Error extracting collection via strategy %s; expected %s instance, but received %s',
            $strategyClass,
            AbstractCollectionMetadata::class,
            $metadata::class
        ));
    }
}
