<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Hal;

use ArrayObject;
use Laminas\Hydrator\HydratorPluginManager;
use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Hal\ResourceGenerator\RouteBasedCollectionStrategy;
use Mezzio\Hal\ResourceGeneratorFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use stdClass;

class ResourceGeneratorFactoryTest extends TestCase
{
    /**
     * @var ObjectProphecy|ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(Metadata\MetadataMap::class)
            ->willReturn($this->prophesize(Metadata\MetadataMap::class));

        $this->container->get(HydratorPluginManager::class)
            ->willReturn($this->prophesize(ContainerInterface::class));

        $this->container->get(LinkGenerator::class)
            ->willReturn($this->prophesize(LinkGenerator::class));
    }

    public function testFactoryRaisesExceptionIfMetadataMapConfigIsNotAnArray()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(new stdClass());

        $object = new ResourceGeneratorFactory();

        $this->expectException(ResourceGenerator\Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('expected an array');
        $object($this->container->reveal());
    }

    public function missingOrEmptyStrategiesConfiguration()
    {
        yield 'missing-top-level' => [[]];
        yield 'missing-second-level' => [[
            'mezzio-hal' => [],
        ]];
        yield 'missing-third-level' => [[
            'mezzio-hal' => [
                'resource-generator' => [],
            ],
        ]];
        yield 'empty-array' => [[
            'mezzio-hal' => [
                'resource-generator' => [
                    'strategies' => [],
                ],
            ],
        ]];
        yield 'empty-array-object' => [[
            'mezzio-hal' => [
                'resource-generator' => [
                    'strategies' => new ArrayObject([]),
                ],
            ],
        ]];
    }

    /**
     * @depends missingOrEmptyStrategiesConfiguration
     */
    public function testFactoryWithoutAnyStrategies(array $config)
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $object = new ResourceGeneratorFactory();

        $resourceGenerator = $object($this->container->reveal());
        self::assertInstanceOf(ResourceGenerator::class, $resourceGenerator);
        self::assertEmpty($resourceGenerator->getStrategies());
    }

    public function invalidStrategiesConfig()
    {
        yield 'null'       => [null];
        yield 'false'      => [false];
        yield 'true'       => [true];
        yield 'zero'       => [0];
        yield 'int'        => [1];
        yield 'zero-float' => [0.0];
        yield 'float'      => [1.1];
        yield 'string'     => ['invalid'];
        yield 'object'     => [(object) ['item' => 'invalid']];
    }

    /**
     * @depends invalidStrategiesConfig
     * @param mixed $strategies
     */
    public function testFactoryRaisesExceptionIfStrategiesConfigIsNonTraversable($strategies)
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'mezzio-hal' => [
                'resource-generator' => [
                    'strategies' => $strategies,
                ],
            ],
        ]);

        $factory = new ResourceGeneratorFactory();

        $this->expectException(ResourceGenerator\Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('strategies configuration');
        $factory($this->container->reveal());
    }

    public function testFactoryWithRouteBasedCollectionStrategy()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(
            [
                'mezzio-hal' => [
                    'resource-generator' => [
                        'strategies' => [
                            RouteBasedCollectionMetadata::class => RouteBasedCollectionStrategy::class,
                        ],
                    ],
                ],
            ]
        );

        $this->container->get(RouteBasedCollectionStrategy::class)->willReturn(
            $this->prophesize(RouteBasedCollectionStrategy::class)
        );

        $object = new ResourceGeneratorFactory();

        $resourceGenerator = $object($this->container->reveal());
        self::assertInstanceOf(ResourceGenerator::class, $resourceGenerator);

        $registeredStrategies = $resourceGenerator->getStrategies();
        self::assertCount(1, $registeredStrategies);
        self::assertArrayHasKey(RouteBasedCollectionMetadata::class, $registeredStrategies);
        self::assertInstanceOf(
            RouteBasedCollectionStrategy::class,
            $registeredStrategies[RouteBasedCollectionMetadata::class]
        );
    }
}
