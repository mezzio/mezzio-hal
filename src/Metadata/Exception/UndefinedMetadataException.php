<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata\Exception;

use RuntimeException;

class UndefinedMetadataException extends RuntimeException implements ExceptionInterface
{
    public static function create($class)
    {
        return new self(sprintf(
            'Unable to retrieve metadata for "%s"; no matching metadata found',
            $class
        ));
    }
}
