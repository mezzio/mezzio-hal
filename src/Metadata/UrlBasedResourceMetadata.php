<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

final class UrlBasedResourceMetadata extends AbstractResourceMetadata
{
    public function __construct(string $class, private readonly string $url, string $extractor, int $maxDepth = 10)
    {
        $this->class     = $class;
        $this->extractor = $extractor;
        $this->maxDepth  = $maxDepth;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
