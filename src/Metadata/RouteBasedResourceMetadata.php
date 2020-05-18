<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

class RouteBasedResourceMetadata extends AbstractResourceMetadata
{
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

    public function __construct(
        string $class,
        string $route,
        string $extractor,
        string $resourceIdentifier = 'id',
        string $routeIdentifierPlaceholder = 'id',
        array $routeParams = [],
        array $identifiersToPlaceholdersMapping = ['id' => 'id']
    ) {
        $this->class = $class;
        $this->route = $route;
        $this->extractor = $extractor;
        $this->resourceIdentifier = $resourceIdentifier;
        $this->routeIdentifierPlaceholder = $routeIdentifierPlaceholder;
        $this->routeParams = $routeParams;
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
