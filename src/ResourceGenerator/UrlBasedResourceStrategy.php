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
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ServerRequestInterface;

class UrlBasedResourceStrategy implements Strategy
{
    use ExtractInstance;

    public function createResource(
        $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : HalResource {
        if (! $metadata instanceof Metadata\UrlBasedResourceMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forMetadata(
                $metadata,
                self::class,
                Metadata\UrlBasedResourceMetadata
            );
        }

        return new HalResource(
            $this->extractInstance($instance, $metadata, $resourceGenerator, $request),
            [new Link('self', $metadata->getUrl())]
        );
    }
}
