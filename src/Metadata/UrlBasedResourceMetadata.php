<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

class UrlBasedResourceMetadata extends AbstractResourceMetadata
{
    /** @var string */
    private $url;

    public function __construct(string $class, string $url, string $extractor, int $maxDepth = 10)
    {
        $this->class     = $class;
        $this->url       = $url;
        $this->extractor = $extractor;
        $this->maxDepth  = $maxDepth;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
