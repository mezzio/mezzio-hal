<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Mezzio\Hal\Metadata\AbstractMetadata;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function class_exists;
use function class_implements;
use function class_parents;
use function get_class;
use function in_array;
use function is_object;
use function is_string;

class ResourceGenerator implements ResourceGeneratorInterface
{
    /** @var ContainerInterface Service locator for hydrators. */
    private $hydrators;

    /** @var LinkGenerator Route-based link generation. */
    private $linkGenerator;

    /** @var Metadata\MetadataMap Metadata on known objects. */
    private $metadataMap;

    /** @var ResourceGenerator\StrategyInterface[] */
    private $strategies = [];

    public function __construct(
        Metadata\MetadataMap $metadataMap,
        ContainerInterface $hydrators,
        LinkGenerator $linkGenerator
    ) {
        $this->metadataMap   = $metadataMap;
        $this->hydrators     = $hydrators;
        $this->linkGenerator = $linkGenerator;
    }

    public function getHydrators(): ContainerInterface
    {
        return $this->hydrators;
    }

    public function getLinkGenerator(): LinkGenerator
    {
        return $this->linkGenerator;
    }

    public function getMetadataMap(): Metadata\MetadataMap
    {
        return $this->metadataMap;
    }

    /**
     * Link a metadata type to a strategy that can create a resource for it.
     *
     * @param string|ResourceGenerator\StrategyInterface $strategy
     */
    public function addStrategy(string $metadataType, $strategy): void
    {
        if (
            ! class_exists($metadataType)
            || ! in_array(AbstractMetadata::class, class_parents($metadataType), true)
        ) {
            throw Exception\UnknownMetadataTypeException::forInvalidMetadataClass($metadataType);
        }

        if (
            is_string($strategy)
            && (
                ! class_exists($strategy)
                || ! in_array(ResourceGenerator\StrategyInterface::class, class_implements($strategy), true)
            )
        ) {
            throw Exception\InvalidStrategyException::forType($strategy);
        }

        if (is_string($strategy)) {
            $strategy = new $strategy();
        }

        if (! $strategy instanceof ResourceGenerator\StrategyInterface) {
            throw Exception\InvalidStrategyException::forInstance($strategy);
        }

        $this->strategies[$metadataType] = $strategy;
    }

    /**
     * Returns the registered strategies.
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    public function fromArray(array $data, ?string $uri = null): HalResource
    {
        $resource = new HalResource($data);

        if (null !== $uri) {
            return $resource->withLink(new Link('self', $uri));
        }

        return $resource;
    }

    /**
     * @param object $instance An object of any type; the type will be checked
     *     against types registered in the metadata map.
     */
    public function fromObject(object $instance, ServerRequestInterface $request, int $depth = 0): HalResource
    {
        if (! is_object($instance)) {
            throw Exception\InvalidObjectException::forNonObject($instance);
        }

        $metadata     = $this->getClassMetadata($instance);
        $metadataType = get_class($metadata);

        if (! isset($this->strategies[$metadataType])) {
            throw Exception\UnknownMetadataTypeException::forMetadata($metadata);
        }

        $strategy = $this->strategies[$metadataType];
        return $strategy->createResource(
            $instance,
            $metadata,
            $this,
            $request,
            $depth
        );
    }

    private function getClassMetadata(object $instance): AbstractMetadata
    {
        $class = get_class($instance);
        if (! $this->metadataMap->has($class)) {
            foreach (class_parents($instance) as $parent) {
                if ($this->metadataMap->has($parent)) {
                    return $this->metadataMap->get($parent);
                }
            }
            throw Exception\InvalidObjectException::forUnknownType($class);
        }

        return $this->metadataMap->get($class);
    }
}
