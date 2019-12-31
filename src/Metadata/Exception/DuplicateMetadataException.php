<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata\Exception;

use DomainException;

use function sprintf;

class DuplicateMetadataException extends DomainException implements ExceptionInterface
{
    public static function create(string $class)
    {
        return new self(sprintf(
            'Attempted to add metadata for class "%s", which already has metadata in the map',
            $class
        ));
    }
}
