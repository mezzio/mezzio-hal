<?php

declare(strict_types=1);

namespace MezzioTest\Hal\Metadata;

use Generator;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\Metadata\MetadataMap;
use PHPUnit\Framework\TestCase;

final class MetadataMapTest extends TestCase
{
    private MetadataMap $map;

    public function setUp(): void
    {
        $this->map = new Metadata\MetadataMap();
    }

    /**
     * @psalm-return Generator<class-string<Metadata\AbstractMetadata>, array{
     *  0: class-string<Metadata\AbstractMetadata>,
     *  1: Metadata\AbstractMetadata
     * }>
     */
    public function validMetadataTypes(): Generator
    {
        $class = Metadata\RouteBasedCollectionMetadata::class;
        yield Metadata\RouteBasedCollectionMetadata::class => [
            $class,
            new Metadata\RouteBasedCollectionMetadata($class, 'foo-bar', 'foo-bar'),
        ];

        yield Metadata\RouteBasedResourceMetadata::class => [
            $class,
            new Metadata\RouteBasedResourceMetadata($class, 'foo-bar', ObjectPropertyHydrator::class),
        ];

        yield Metadata\UrlBasedCollectionMetadata::class => [
            $class,
            new Metadata\UrlBasedCollectionMetadata($class, 'foo-bar', 'foo-bar'),
        ];

        yield Metadata\UrlBasedResourceMetadata::class => [
            $class,
            new Metadata\UrlBasedResourceMetadata($class, 'foo-bar', ObjectPropertyHydrator::class),
        ];
    }

    /**
     * @dataProvider validMetadataTypes
     */
    public function testCanAggregateAnyMetadataType(string $class, Metadata\AbstractMetadata $metadata): void
    {
        self::assertFalse($this->map->has($class));
        $this->map->add($metadata);
        self::assertTrue($this->map->has($class));
        self::assertSame($metadata, $this->map->get($class));
    }

    public function testAddWillRaiseUndefinedClassExceptionIfClassDoesNotExist(): void
    {
        $metadata = $this->createMock(Metadata\AbstractMetadata::class);
        $metadata
            ->method('getClass')
            ->willReturn('undefined-class');

        $this->expectException(Metadata\Exception\UndefinedClassException::class);
        $this->expectExceptionMessage('undefined-class');
        $this->map->add($metadata);
    }

    public function testAddWillRaiseDuplicateMetadataExceptionWhenDuplicateMetadataEncountered(): void
    {
        $first = $this->createMock(Metadata\AbstractMetadata::class);
        $first
            ->method('getClass')
            ->willReturn(self::class);

        $this->map->add($first);
        self::assertSame($first, $this->map->get(self::class));

        $second = $this->createMock(Metadata\AbstractMetadata::class);
        $second
            ->method('getClass')
            ->willReturn(self::class);

        $this->expectException(Metadata\Exception\DuplicateMetadataException::class);
        $this->expectExceptionMessage(self::class);
        $this->map->add($second);
    }

    public function testGetWilRaiseUndefinedMetadataExceptionIfClassNotPresentInMap(): void
    {
        $this->expectException(Metadata\Exception\UndefinedMetadataException::class);
        $this->expectExceptionMessage(self::class);
        $this->map->get(self::class);
    }
}
