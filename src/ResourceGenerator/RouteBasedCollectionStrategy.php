<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

use function array_merge;

class RouteBasedCollectionStrategy implements StrategyInterface
{
    use ExtractCollectionTrait, GenerateSelfLinkTrait {
        GenerateSelfLinkTrait::generateSelfLink insteadof ExtractCollectionTrait;
    }

    public function createResource(
        object $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ): HalResource {
        if (! $metadata instanceof Metadata\RouteBasedCollectionMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forMetadata(
                $metadata,
                self::class,
                Metadata\RouteBasedCollectionMetadata::class
            );
        }

        if (! $instance instanceof Traversable) {
            throw Exception\InvalidCollectionException::fromInstance($instance, static::class);
        }

        return $this->extractCollection($instance, $metadata, $resourceGenerator, $request, $depth);
    }

    /**
     * @param string $rel Relation to use when creating Link
     * @param int $page Page number for generated link
     * @param Metadata\AbstractCollectionMetadata $metadata Used to provide the
     *     base URL, pagination parameter, and type of pagination used (query
     *     string, path parameter)
     * @param ResourceGeneratorInterface $resourceGenerator Used to retrieve link
     *     generator in order to generate link based on routing information.
     * @param ServerRequestInterface $request Passed to link generator when
     *     generating link based on routing information.
     */
    protected function generateLinkForPage(
        string $rel,
        int $page,
        Metadata\AbstractCollectionMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request
    ): Link {
        $route           = $metadata->getRoute();
        $paginationType  = $metadata->getPaginationParamType();
        $paginationParam = $metadata->getPaginationParam();
        $routeParams     = $metadata->getRouteParams();
        $queryStringArgs = $metadata->getQueryStringArguments();

        $paramsWithPage = [$paginationParam => $page];
        $routeParams    = $paginationType === Metadata\AbstractCollectionMetadata::TYPE_PLACEHOLDER
            ? array_merge($routeParams, $paramsWithPage)
            : $routeParams;
        $queryParams    = $paginationType === Metadata\AbstractCollectionMetadata::TYPE_QUERY
            ? array_merge($queryStringArgs, $paramsWithPage)
            : $queryStringArgs;

        return $resourceGenerator
            ->getLinkGenerator()
            ->fromRoute(
                $rel,
                $request,
                $route,
                $routeParams,
                $queryParams
            );
    }

    /**
     * @param Metadata\AbstractCollectionMetadata $metadata Provides base URL
     *     for self link.
     * @param ResourceGeneratorInterface $resourceGenerator Used to retrieve link
     *     generator in order to generate link based on routing information.
     * @param ServerRequestInterface $request Passed to link generator when
     *     generating link based on routing information.
     * @return Link
     */
    protected function generateSelfLink(
        Metadata\AbstractCollectionMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request
    ) {
        $routeParams     = $metadata->getRouteParams() ?? [];
        $queryStringArgs = array_merge($request->getQueryParams() ?? [], $metadata->getQueryStringArguments() ?? []);

        return $resourceGenerator
            ->getLinkGenerator()
            ->fromRoute(
                'self',
                $request,
                $metadata->getRoute(),
                $routeParams,
                $queryStringArgs
            );
    }
}
