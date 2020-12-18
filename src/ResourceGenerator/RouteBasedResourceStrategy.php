<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ServerRequestInterface;

class RouteBasedResourceStrategy implements StrategyInterface
{
    use ExtractInstanceTrait;

    public function createResource(
        $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : HalResource {
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
            $request
        );

        $routeParams        = $metadata->getRouteParams();
        $resourceIdentifier = $metadata->getResourceIdentifier();
        $routeIdentifier    = $metadata->getRouteIdentifierPlaceholder();

        if (isset($data[$resourceIdentifier])) {
            $routeParams[$routeIdentifier] = $data[$resourceIdentifier];
        }

        // Inject all scalar entity keys automatically into route parameters
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $routeParams[$key] = $value;
            }
        }

        return new HalResource($data, [
            $resourceGenerator->getLinkGenerator()->fromRoute(
                'self',
                $request,
                $metadata->getRoute(),
                $routeParams
            )
        ]);
    }
}
