<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use Laminas\Hydrator\ExtractionInterface;
use RuntimeException;

use function get_debug_type;
use function sprintf;

/** @final */
class InvalidExtractorException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $extractor
     */
    public static function fromInstance($extractor): self
    {
        return new self(sprintf(
            'Invalid extractor "%s" provided in metadata; does not implement %s',
            get_debug_type($extractor),
            ExtractionInterface::class
        ));
    }
}
