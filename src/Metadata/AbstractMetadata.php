<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

use Mezzio\Hal\LinkCollection;

abstract class AbstractMetadata
{
    use LinkCollection;

    /** @var string */
    protected $class;

    public function getClass(): string
    {
        return $this->class;
    }

    public function hasReachedMaxDepth(int $currentDepth): bool
    {
        return false;
    }
}
