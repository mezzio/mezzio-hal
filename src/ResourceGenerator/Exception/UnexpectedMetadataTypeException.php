<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator\Exception;

use Mezzio\Hal\Metadata\AbstractCollectionMetadata;
use Mezzio\Hal\Metadata\AbstractMetadata;
use RuntimeException;

class UnexpectedMetadataTypeException extends RuntimeException implements ExceptionInterface
{
    public static function forMetadata(AbstractMetadata $metadata, string $strategy, string $expected) : self
    {
        return new self(sprintf(
            'Unexpected metadata of type %s was mapped to %s (expects %s)',
            get_class($metadata),
            $strategy,
            $expected
        ));
    }

    public static function forCollection(AbstractMetadata $metadata, string $strategyClass) : self
    {
        return new self(sprintf(
            'Error extracting collection via strategy %s; expected %s instance, but received %s',
            $strategyClass,
            AbstractCollectionMetadata::class,
            get_class($metadata)
        ));
    }
}
