<?php

declare(strict_types=1);

namespace MezzioTest\Hal\Metadata;

use Generator;
use Mezzio\Hal\Metadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see MockObject
 */
class MetadataMapTest extends TestCase
{
    /** @psalm-var non-empty-list<class-string<Metadata\AbstractMetadata>> */
    private $metadataClasses = [
        Metadata\AbstractMetadata::class,
        Metadata\AbstractCollectionMetadata::class,
        Metadata\AbstractResourceMetadata::class,
        Metadata\RouteBasedCollectionMetadata::class,
        Metadata\RouteBasedResourceMetadata::class,
        Metadata\UrlBasedCollectionMetadata::class,
        Metadata\UrlBasedResourceMetadata::class,
    ];

    /** @var Metadata\MetadataMap */
    private $map;

    public function setUp(): void
    {
        $this->map = new Metadata\MetadataMap();
    }

    /**
     * @psalm-return Generator<class-string<Metadata\AbstractMetadata>, array{
     *  0: class-string<Metadata\AbstractMetadata>,
     *  1: Metadata\AbstractMetadata&MockObject
     * }>
     */
    public function validMetadataTypes(): Generator
    {
        foreach ($this->metadataClasses as $class) {
            $metadata = $this->createMock($class);
            $metadata
                ->method('getClass')
                ->willReturn($class);

            yield $class => [$class, $metadata];
        }
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
