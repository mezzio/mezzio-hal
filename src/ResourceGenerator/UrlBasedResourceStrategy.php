<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

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
        $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ) : HalResource {
        if (! $metadata instanceof Metadata\UrlBasedResourceMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forMetadata(
                $metadata,
                self::class,
                Metadata\UrlBasedResourceMetadata::class
            );
        }

        return new HalResource(
            $this->extractInstance($instance, $metadata, $resourceGenerator, $request),
            [new Link('self', $metadata->getUrl())]
        );
    }
}
