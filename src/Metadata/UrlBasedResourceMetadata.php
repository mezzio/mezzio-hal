<?php

declare(strict_types=1);

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
