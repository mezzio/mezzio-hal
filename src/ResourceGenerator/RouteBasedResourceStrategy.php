<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function is_scalar;

class RouteBasedResourceStrategy implements StrategyInterface
{
    use ExtractInstanceTrait;

    public function createResource(
        object $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ): HalResource {
        if (! $metadata instanceof Metadata\RouteBasedResourceMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forMetadata(
                $metadata,
                self::class,
                Metadata\RouteBasedResourceMetadata::class
            );
        }

        $data = $this->extractInstance(
            $instance,
            $metadata,
            $resourceGenerator,
            $request,
            $depth
        );

        $routeParams    = $metadata->getRouteParams();
        $placeholderMap = $metadata->getIdentifiersToPlaceholdersMapping();

        // Inject all scalar entity keys automatically into route parameters
        foreach ($data as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }

            if (array_key_exists($key, $placeholderMap)) {
                $routeParams[$placeholderMap[$key]] = $value;
                continue;
            }

            $routeParams[$key] = $value;
        }

        if ($metadata->hasReachedMaxDepth($depth)) {
            $data = [];
        }

        return new HalResource($data, [
            $resourceGenerator->getLinkGenerator()->fromRoute(
                'self',
                $request,
                $metadata->getRoute(),
                $routeParams
            ),
        ]);
    }
}
