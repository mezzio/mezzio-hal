<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

abstract class AbstractResourceMetadata extends AbstractMetadata
{
    /**
     * Service name of an ExtractionInterface implementation to use when
     * extracting a resource of this type.
     *
     * @var string
     */
    protected $extractor;

    /** @var int */
    protected $maxDepth;

    public function getExtractor(): string
    {
        return $this->extractor;
    }

    public function hasReachedMaxDepth(int $currentDepth): bool
    {
        return $currentDepth > $this->maxDepth;
    }
}
