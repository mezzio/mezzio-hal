<?php

namespace MezzioTest\Hal\Metadata;

use Generator;
use Mezzio\Hal\Metadata;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MetadataMapTest extends TestCase
{
    use ProphecyTrait;

    /** @psalm-var string[] */
    private $metadataClasses = [
        Metadata\AbstractMetadata::class,
        Metadata\AbstractCollectionMetadata::class,
        Metadata\AbstractResourceMetadata::class,
        Metadata\RouteBasedCollectionMetadata::class,
        Metadata\RouteBasedResourceMetadata::class,
        Metadata\UrlBasedCollectionMetadata::class,
        Metadata\UrlBasedResourceMetadata::class,
    ];

    public function setUp(): void
    {
        $this->map = new Metadata\MetadataMap();
    }

    /**
     * @psalm-return Generator<string, array{0: string, 1: object}, mixed, void>
     */
    public function validMetadataTypes(): Generator
    {
        foreach ($this->metadataClasses as $class) {
            $metadata = $this->prophesize($class);
            $metadata->getClass()->willReturn($class);
            yield $class => [$class, $metadata->reveal()];
        }
    }

    /**
     * @dataProvider validMetadataTypes
     */
    public function testCanAggregateAnyMetadataType(string $class, Metadata\AbstractMetadata $metadata): void
    {
        $this->assertFalse($this->map->has($class));
        $this->map->add($metadata);
        $this->assertTrue($this->map->has($class));
        $this->assertSame($metadata, $this->map->get($class));
    }

    public function testAddWillRaiseUndefinedClassExceptionIfClassDoesNotExist(): void
    {
        $metadata = $this->prophesize(Metadata\AbstractMetadata::class);
        $metadata->getClass()->willReturn('undefined-class');

        $this->expectException(Metadata\Exception\UndefinedClassException::class);
        $this->expectExceptionMessage('undefined-class');
        $this->map->add($metadata->reveal());
    }

    public function testAddWillRaiseDuplicateMetadataExceptionWhenDuplicateMetadataEncountered(): void
    {
        $first = $this->prophesize(Metadata\AbstractMetadata::class);
        $first->getClass()->willReturn(self::class);

        $this->map->add($first->reveal());
        $this->assertSame($first->reveal(), $this->map->get(self::class));

        $second = $this->prophesize(Metadata\AbstractMetadata::class);
        $second->getClass()->willReturn(self::class);

        $this->expectException(Metadata\Exception\DuplicateMetadataException::class);
        $this->expectExceptionMessage(self::class);
        $this->map->add($second->reveal());
    }

    public function testGetWilRaiseUndefinedMetadataExceptionIfClassNotPresentInMap(): void
    {
        $this->expectException(Metadata\Exception\UndefinedMetadataException::class);
        $this->expectExceptionMessage(self::class);
        $this->map->get(self::class);
    }
}
