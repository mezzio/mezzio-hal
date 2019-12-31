<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator\Exception;

use Laminas\Hydrator\ExtractionInterface;
use RuntimeException;

class InvalidExtractorException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $extractor
     */
    public static function fromInstance($extractor) : self
    {
        return new self(sprintf(
            'Invalid extractor "%s" provided in metadata; does not implement %s',
            is_object($extractor) ? get_class($extractor) : gettype($extractor),
            ExtractionInterface::class
        ));
    }
}
