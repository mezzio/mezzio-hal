<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

class RouteBasedResourceMetadata extends AbstractResourceMetadata
{
    private const DEFAULT_RESOURCE_ID = 'id';

    /** @var array */
    private $identifiersToPlaceHoldersMapping;

    /** @var string */
    private $resourceIdentifier;

    /** @var string */
    private $route;

    /** @var array */
    private $routeParams;

    public function __construct(
        string $class,
        string $route,
        string $extractor,
        string $resourceIdentifier = self::DEFAULT_RESOURCE_ID,
        array $routeParams = [],
        array $identifiersToPlaceholdersMapping = [],
        int $maxDepth = 10
    ) {
        $this->class                            = $class;
        $this->route                            = $route;
        $this->extractor                        = $extractor;
        $this->resourceIdentifier               = $resourceIdentifier;
        $this->routeParams                      = $routeParams;
        $this->identifiersToPlaceHoldersMapping = $identifiersToPlaceholdersMapping;
        $this->maxDepth                         = $maxDepth;
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
