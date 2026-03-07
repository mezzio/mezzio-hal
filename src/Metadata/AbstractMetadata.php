<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

use Mezzio\Hal\LinkCollection;

abstract class AbstractMetadata
{
    use LinkCollection;

    protected string $class;

    public function getClass(): string
    {
        return $this->class;
    }

    public function hasReachedMaxDepth(int $currentDepth): bool
    {
        return false;
    }
}
