<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

final class RouteBasedResourceMetadata extends AbstractResourceMetadata
{
    private const DEFAULT_RESOURCE_ID = 'id';

    public function __construct(
        string $class,
        private readonly string $route,
        string $extractor,
        private readonly string $resourceIdentifier = self::DEFAULT_RESOURCE_ID,
        private array $routeParams = [],
        private readonly array $identifiersToPlaceHoldersMapping = [],
        int $maxDepth = 10
    ) {
        $this->class     = $class;
        $this->extractor = $extractor;
        $this->maxDepth  = $maxDepth;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getIdentifiersToPlaceholdersMapping(): array
    {
        return $this->identifiersToPlaceHoldersMapping;
    }

    /**
     * This method has been kept for BC and should be deprecated.
     */
    public function getResourceIdentifier(): string
    {
        return $this->resourceIdentifier;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }
}
