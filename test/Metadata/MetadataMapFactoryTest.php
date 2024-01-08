<?php

declare(strict_types=1);

namespace MezzioTest\Hal\Metadata;

use Generator;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\Metadata\Exception\InvalidConfigException;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\MetadataMapFactory;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadataFactory;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadataFactory;
use Mezzio\Hal\Metadata\UrlBasedCollectionMetadata;
use Mezzio\Hal\Metadata\UrlBasedCollectionMetadataFactory;
use Mezzio\Hal\Metadata\UrlBasedResourceMetadata;
use Mezzio\Hal\Metadata\UrlBasedResourceMetadataFactory;
use MezzioTest\Hal\TestAsset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use stdClass;

class MetadataMapFactoryTest extends TestCase
{
    /** @var MetadataMapFactory */
    private $factory;

    /** @var ContainerInterface&MockObject */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory   = new MetadataMapFactory();
    }

    public function testFactoryReturnsEmptyMetadataMapWhenNoConfigServicePresent(): void
    {
        $metadataMap = ($this->factory)($this->container);

        $r = new ReflectionProperty($metadataMap, 'map');
        self::assertSame([], $r->getValue($metadataMap));
    }

    public function testFactoryReturnsEmptyMetadataMapWhenConfigServiceHasNoMetadataMapEntries(): void
    {
        $this->populateConfiguration($this->container, []);

        $metadataMap = ($this->factory)($this->container);

        $r = new ReflectionProperty($metadataMap, 'map');
        self::assertSame([], $r->getValue($metadataMap));
    }

    public function testFactoryRaisesExceptionIfMetadataMapConfigIsNotAnArray(): void
    {
        $this->populateConfiguration($this->container, [MetadataMap::class => 'nope']);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('expected an array');
        ($this->factory)($this->container);
    }

    public function testFactoryRaisesExceptionIfAnyMetadataIsMissingAClassEntry(): void
    {
        $this->populateConfiguration($this->container, [MetadataMap::class => [['nope']]]);
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('missing "__class__"');
        ($this->factory)($this->container);
    }

    public function testFactoryRaisesExceptionIfTheMetadataClassDoesNotExist(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__' => 'not-a-class',
                ],
            ],
        ]);
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid metadata class provided');
        ($this->factory)($this->container);
    }

    public function testFactoryRaisesExceptionIfTheMetadataClassIsNotAnAbstractMetadataType(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__' => self::class,
                ],
            ],
        ]);
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('does not extend ' . Metadata\AbstractMetadata::class);
        ($this->factory)($this->container);
    }

    public function testFactoryRaisesExceptionIfMetadataClassDoesNotHaveACreationMethodInTheFactory(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__' => TestAsset\TestMetadata::class,
                ],
            ],
        ]);
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('please provide a factory in your configuration');
        ($this->factory)($this->container);
    }

    public function testFactoryRaisesExceptionIfMetadataFactoryDoesNotImplementFactoryInterface(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                ['__class__' => TestAsset\TestMetadata::class],
            ],
            'mezzio-hal'       => [
                'metadata-factories' => [
                    TestAsset\TestMetadata::class => stdClass::class,
                ],
            ],
        ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('is not a valid metadata factory class; does not implement');
        ($this->factory)($this->container);
    }

    public function invalidMetadata(): Generator
    {
        $types = [
            UrlBasedResourceMetadata::class,
            UrlBasedCollectionMetadata::class,
            RouteBasedResourceMetadata::class,
            RouteBasedCollectionMetadata::class,
        ];

        foreach ($types as $type) {
            yield $type => [['__class__' => $type], $type];
        }
    }

    /**
     * @dataProvider invalidMetadata
     */
    public function testFactoryRaisesExceptionIfMetadataIsMissingRequiredElements(
        array $metadata,
        string $expectExceptionString
    ): void {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [$metadata],
            'mezzio-hal'       => [
                'metadata-factories' => [
                    RouteBasedCollectionMetadata::class => RouteBasedCollectionMetadataFactory::class,
                    RouteBasedResourceMetadata::class   => RouteBasedResourceMetadataFactory::class,
                    UrlBasedCollectionMetadata::class   => UrlBasedCollectionMetadataFactory::class,
                    UrlBasedResourceMetadata::class     => UrlBasedResourceMetadataFactory::class,
                ],
            ],
        ]);
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($expectExceptionString);
        ($this->factory)($this->container);
    }

    public function testFactoryCanMapUrlBasedResourceMetadata(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__'      => UrlBasedResourceMetadata::class,
                    'resource_class' => stdClass::class,
                    'url'            => '/test/foo',
                    'extractor'      => 'ObjectProperty',
                ],
            ],
            'mezzio-hal'       => [
                'metadata-factories' => [
                    UrlBasedResourceMetadata::class => UrlBasedResourceMetadataFactory::class,
                ],
            ],
        ]);

        $metadataMap = ($this->factory)($this->container);
        self::assertTrue($metadataMap->has(stdClass::class));
        $metadata = $metadataMap->get(stdClass::class);

        self::assertInstanceOf(UrlBasedResourceMetadata::class, $metadata);
        self::assertSame(stdClass::class, $metadata->getClass());
        self::assertSame('ObjectProperty', $metadata->getExtractor());
        self::assertSame('/test/foo', $metadata->getUrl());
    }

    public function testFactoryCanMapUrlBasedCollectionMetadata(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__'             => UrlBasedCollectionMetadata::class,
                    'collection_class'      => stdClass::class,
                    'collection_relation'   => 'foo',
                    'url'                   => '/test/foo',
                    'pagination_param'      => 'p',
                    'pagination_param_type' => Metadata\AbstractCollectionMetadata::TYPE_PLACEHOLDER,
                ],
            ],
            'mezzio-hal'       => [
                'metadata-factories' => [
                    UrlBasedCollectionMetadata::class => UrlBasedCollectionMetadataFactory::class,
                ],
            ],
        ]);

        $metadataMap = ($this->factory)($this->container);
        self::assertTrue($metadataMap->has(stdClass::class));
        $metadata = $metadataMap->get(stdClass::class);

        self::assertInstanceOf(UrlBasedCollectionMetadata::class, $metadata);
        self::assertSame(stdClass::class, $metadata->getClass());
        self::assertSame('foo', $metadata->getCollectionRelation());
        self::assertSame('/test/foo', $metadata->getUrl());
        self::assertSame('p', $metadata->getPaginationParam());
        self::assertSame(Metadata\AbstractCollectionMetadata::TYPE_PLACEHOLDER, $metadata->getPaginationParamType());
    }

    public function testFactoryCanMapRouteBasedResourceMetadata(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__'                           => RouteBasedResourceMetadata::class,
                    'resource_class'                      => stdClass::class,
                    'route'                               => 'foo',
                    'extractor'                           => 'ObjectProperty',
                    'resource_identifier'                 => 'foo_id',
                    'route_params'                        => ['foo' => 'bar'],
                    'identifiers_to_placeholders_mapping' => [
                        'bar' => 'bar_value',
                        'baz' => 'baz_value',
                    ],
                ],
            ],
            'mezzio-hal'       => [
                'metadata-factories' => [
                    RouteBasedResourceMetadata::class => RouteBasedResourceMetadataFactory::class,
                ],
            ],
        ]);

        $metadataMap = ($this->factory)($this->container);
        self::assertTrue($metadataMap->has(stdClass::class));
        $metadata = $metadataMap->get(stdClass::class);

        self::assertInstanceOf(RouteBasedResourceMetadata::class, $metadata);
        self::assertSame(stdClass::class, $metadata->getClass());
        self::assertSame('ObjectProperty', $metadata->getExtractor());
        self::assertSame('foo', $metadata->getRoute());
        self::assertSame('foo_id', $metadata->getResourceIdentifier());
        self::assertSame(['foo' => 'bar'], $metadata->getRouteParams());
        self::assertSame([
            'bar' => 'bar_value',
            'baz' => 'baz_value',
        ], $metadata->getIdentifiersToPlaceholdersMapping());
    }

    public function testFactoryCanMapRouteBasedCollectionMetadata(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                [
                    '__class__'              => RouteBasedCollectionMetadata::class,
                    'collection_class'       => stdClass::class,
                    'collection_relation'    => 'foo',
                    'route'                  => 'foo',
                    'pagination_param'       => 'p',
                    'pagination_param_type'  => Metadata\AbstractCollectionMetadata::TYPE_PLACEHOLDER,
                    'route_params'           => ['foo' => 'bar'],
                    'query_string_arguments' => ['baz' => 'bat'],
                ],
            ],
            'mezzio-hal'       => [
                'metadata-factories' => [
                    RouteBasedCollectionMetadata::class => RouteBasedCollectionMetadataFactory::class,
                ],
            ],
        ]);

        $metadataMap = ($this->factory)($this->container);
        self::assertTrue($metadataMap->has(stdClass::class));
        $metadata = $metadataMap->get(stdClass::class);

        self::assertInstanceOf(RouteBasedCollectionMetadata::class, $metadata);
        self::assertSame(stdClass::class, $metadata->getClass());
        self::assertSame('foo', $metadata->getCollectionRelation());
        self::assertSame('foo', $metadata->getRoute());
        self::assertSame('p', $metadata->getPaginationParam());
        self::assertSame(Metadata\AbstractCollectionMetadata::TYPE_PLACEHOLDER, $metadata->getPaginationParamType());
        self::assertSame(['foo' => 'bar'], $metadata->getRouteParams());
        self::assertSame(['baz' => 'bat'], $metadata->getQueryStringArguments());
    }

    public function testFactoryCanCreateMetadataViaFactoryMethod(): void
    {
        $this->populateConfiguration($this->container, [
            MetadataMap::class => [
                ['__class__' => TestAsset\TestMetadata::class],
            ],
        ]);

        $this->factory = new TestAsset\TestMetadataMapFactory();

        $metadataMap = ($this->factory)($this->container);
        self::assertTrue($metadataMap->has(stdClass::class));
        $metadata = $metadataMap->get(stdClass::class);

        self::assertInstanceOf(TestAsset\TestMetadata::class, $metadata);
    }

    /**
     * @param ContainerInterface&MockObject $container
     */
    private function populateConfiguration(ContainerInterface $container, array $config): void
    {
        $container
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container
            ->method('get')
            ->with('config')
            ->willReturn($config);
    }
}
