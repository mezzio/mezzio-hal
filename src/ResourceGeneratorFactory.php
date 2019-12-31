<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal;

use ArrayAccess;
use Laminas\Hydrator\HydratorPluginManager;
use Mezzio\Hal\ResourceGenerator\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;
use Traversable;

class ResourceGeneratorFactory
{
    public function __invoke(ContainerInterface $container) : ResourceGenerator
    {
        $generator = new ResourceGenerator(
            $container->get(Metadata\MetadataMap::class),
            $container->get(HydratorPluginManager::class),
            $container->get(LinkGenerator::class)
        );

        $this->injectStrategies($container, $generator);

        return $generator;
    }

    /**
     * @throws InvalidConfigException if the config service is not an array or
     *     ArrayAccess implementation.
     * @throws InvalidConfigException if the configured strategies value is not
     *     an array or traversable.
     */
    private function injectStrategies(ContainerInterface $container, ResourceGenerator $generator) : void
    {
        if (! $container->has('config')) {
            return;
        }

        $config = $container->get('config');

        if (! is_array($config) && ! $config instanceof ArrayAccess) {
            throw InvalidConfigException::dueToNonArray($config);
        }

        if (! isset($config['mezzio-hal']['resource-generator']['strategies'])) {
            return;
        }

        $strategies = $config['mezzio-hal']['resource-generator']['strategies'];

        if (! is_array($strategies) && ! $strategies instanceof Traversable) {
            throw InvalidConfigException::dueToInvalidStrategies($strategies);
        }

        foreach ($strategies as $metadataType => $strategy) {
            $generator->addStrategy(
                $metadataType,
                $container->get($strategy)
            );
        }
    }
}
