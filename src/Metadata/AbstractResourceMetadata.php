<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

abstract class AbstractResourceMetadata extends AbstractMetadata
{
    /**
     * Service name of an ExtractionInterface implementation to use when
     * extracting a resource of this type.
     * @var string
     */
    protected $extractor;

    /** @var int */
    protected $maxDepth;

    public function getExtractor() : string
    {
        return $this->extractor;
    }

    public function hasReachedMaxDepth(int $currentDepth): bool
    {
        return $currentDepth > $this->maxDepth;
    }
}
