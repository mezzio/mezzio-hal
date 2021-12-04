<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlBasedResourceStrategy implements StrategyInterface
{
    use ExtractInstanceTrait;

    public function createResource(
        object $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ): HalResource {
        if (! $metadata instanceof Metadata\UrlBasedResourceMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forMetadata(
                $metadata,
                self::class,
                Metadata\UrlBasedResourceMetadata::class
            );
        }

        return new HalResource(
            $this->extractInstance($instance, $metadata, $resourceGenerator, $request, $depth),
            [new Link('self', $metadata->getUrl())]
        );
    }
}
