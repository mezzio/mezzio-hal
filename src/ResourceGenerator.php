<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Mezzio\Hal\Metadata\AbstractMetadata;
use Mezzio\Hal\ResourceGenerator\StrategyInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function class_exists;
use function class_implements;
use function class_parents;
use function in_array;
use function is_string;

class ResourceGenerator implements ResourceGeneratorInterface
{
    /** @var array<string, StrategyInterface> */
    private array $strategies = [];

    /**
     * @param Metadata\MetadataMap $metadataMap Metadata on known objects.
     * @param ContainerInterface $hydrators Service locator for hydrators.
     * @param LinkGenerator $linkGenerator Route-based link generation.
     */
    public function __construct(
        private Metadata\MetadataMap $metadataMap,
        private ContainerInterface $hydrators,
        private LinkGenerator $linkGenerator
    ) {
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
     * @param class-string<StrategyInterface>|StrategyInterface $strategy
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
                || ! in_array(StrategyInterface::class, class_implements($strategy), true)
            )
        ) {
            throw Exception\InvalidStrategyException::forType($strategy);
        }

        if (is_string($strategy)) {
            $strategy = new $strategy();
        }

        if (! $strategy instanceof StrategyInterface) {
            throw Exception\InvalidStrategyException::forInstance($strategy);
        }

        $this->strategies[$metadataType] = $strategy;
    }

    /**
     * Returns the registered strategies.
     *
     * @return array<string, StrategyInterface>
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
        $metadata     = $this->getClassMetadata($instance);
        $metadataType = $metadata::class;

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
        $class = $instance::class;
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
