<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use Laminas\Hydrator\ExtractionInterface;
use RuntimeException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class InvalidExtractorException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $extractor
     */
    public static function fromInstance($extractor): self
    {
        return new self(sprintf(
            'Invalid extractor "%s" provided in metadata; does not implement %s',
            is_object($extractor) ? get_class($extractor) : gettype($extractor),
            ExtractionInterface::class
        ));
    }
}
