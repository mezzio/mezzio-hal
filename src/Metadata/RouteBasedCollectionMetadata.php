<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

class RouteBasedCollectionMetadata extends AbstractCollectionMetadata
{
    /**
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $queryStringArguments
     */
    public function __construct(
        string $class,
        string $collectionRelation,
        private string $route,
        string $paginationParam = 'page',
        string $paginationParamType = self::TYPE_QUERY,
        private array $routeParams = [],
        private array $queryStringArguments = []
    ) {
        $this->class               = $class;
        $this->collectionRelation  = $collectionRelation;
        $this->paginationParam     = $paginationParam;
        $this->paginationParamType = $paginationParamType;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    /** @return array<string, mixed> */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /** @return array<string, mixed> */
    public function getQueryStringArguments(): array
    {
        return $this->queryStringArguments;
    }

    /**
     * Allow run-time overriding/injection of route parameters.
     *
     * In particular, this is useful for setting a parent identifier
     * in the route when dealing with child resources.
     *
     * @param array<string, mixed> $routeParams
     */
    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    /**
     * Allow run-time overriding/injection of query string arguments.
     *
     * In particular, this is useful for setting query string arguments for
     * searches, sorts, limits, etc.
     *
     * @param array<string, mixed> $query
     */
    public function setQueryStringArguments(array $query): void
    {
        $this->queryStringArguments = $query;
    }
}
