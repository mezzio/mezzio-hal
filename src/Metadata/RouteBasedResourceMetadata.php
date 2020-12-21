<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

class RouteBasedResourceMetadata extends AbstractResourceMetadata
{
    private const DEFAULT_RESOURCE_ID = 'id';
    private const DEFAULT_ROUTE_ID_PLACEHOLDER = 'id';

    /** @var array */
    private $identifiersToPlaceHoldersMapping;

    /** @var string */
    private $resourceIdentifier;

    /** @var string */
    private $route;

    /** @var string */
    private $routeIdentifierPlaceholder;

    /** @var array */
    private $routeParams;

    /**
     * @param string $routeIdentifierPlaceholder Deprecated; use $identifiersToPlaceholdersMapping instead.
     */
    public function __construct(
        string $class,
        string $route,
        string $extractor,
        string $resourceIdentifier = self::DEFAULT_RESOURCE_ID,
        string $routeIdentifierPlaceholder = self::DEFAULT_ROUTE_ID_PLACEHOLDER,
        array $routeParams = [],
        array $identifiersToPlaceholdersMapping = []
    ) {
        $this->class = $class;
        $this->route = $route;
        $this->extractor = $extractor;
        $this->routeParams = $routeParams;

        $this->resourceIdentifier = $resourceIdentifier;
        $this->routeIdentifierPlaceholder = $routeIdentifierPlaceholder;

        if (! array_key_exists($resourceIdentifier, $identifiersToPlaceholdersMapping)) {
            $identifiersToPlaceholdersMapping[$resourceIdentifier] = $routeIdentifierPlaceholder;
        }

        $this->identifiersToPlaceHoldersMapping = $identifiersToPlaceholdersMapping;
    }

    public function getRoute() : string
    {
        return $this->route;
    }

    public function getIdentifiersToPlaceholdersMapping() : array
    {
        return $this->identifiersToPlaceHoldersMapping;
    }

    /**
     * This method has been kept for BC and should be deprecated.
     *
     * @return string
     */
    public function getResourceIdentifier() : string
    {
        return $this->resourceIdentifier;
    }

    /**
     * This method has been kept for BC and should be deprecated.
     *
     * @return string
     */
    public function getRouteIdentifierPlaceholder() : string
    {
        return $this->routeIdentifierPlaceholder;
    }

    public function getRouteParams() : array
    {
        return $this->routeParams;
    }

    public function setRouteParams(array $routeParams) : void
    {
        $this->routeParams = $routeParams;
    }
}
