<?php

declare(strict_types=1);

namespace MezzioTest\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Hal\ResourceGenerator;
use MezzioTest\Hal\Assertions;
use MezzioTest\Hal\PHPUnitDeprecatedAssertions;
use MezzioTest\Hal\TestAsset;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function array_shift;

class NestedCollectionResourceGenerationTest extends TestCase
{
    use Assertions;

    use PHPUnitDeprecatedAssertions;

    use ProphecyTrait;

    public function testNestedCollectionIsEmbeddedAsAnArrayNotAHalCollection(): void
    {
        $collection    = $this->createCollection();
        $foo           = new TestAsset\FooBar();
        $foo->id       = 101010;
        $foo->foo      = 'foo';
        $foo->children = $collection;

        $request       = $this->prophesize(ServerRequestInterface::class);
        $metadataMap   = $this->createMetadataMap();
        $hydrators     = $this->createHydrators();
        $linkGenerator = $this->createLinkGenerator($request);

        $generator = new ResourceGenerator(
            $metadataMap->reveal(),
            $hydrators->reveal(),
            $linkGenerator->reveal()
        );

        $generator->addStrategy(
            RouteBasedResourceMetadata::class,
            ResourceGenerator\RouteBasedResourceStrategy::class
        );

        $generator->addStrategy(
            RouteBasedCollectionMetadata::class,
            ResourceGenerator\RouteBasedCollectionStrategy::class
        );

        $resource = $generator->fromObject($foo, $request->reveal());
        $this->assertInstanceOf(HalResource::class, $resource);

        $childCollection = $resource->getElement('children');
        $this->assertInternalType('array', $childCollection);

        foreach ($childCollection as $child) {
            $this->assertInstanceOf(HalResource::class, $child);
            $selfLinks = $child->getLinksByRel('self');
            $this->assertInternalType('array', $selfLinks);
            $this->assertNotEmpty($selfLinks);
            $selfLink = array_shift($selfLinks);
            $this->assertStringContainsString('/child/', $selfLink->getHref());
        }
    }

    private function createCollection(): TestAsset\Collection
    {
        $items = [];
        for ($i = 1; $i < 11; $i += 1) {
            $item          = new TestAsset\Child();
            $item->id      = $i;
            $item->message = 'ack';
            $items[]       = $item;
        }
        return new TestAsset\Collection($items);
    }

    /**
     * @psalm-return ObjectProphecy<MetadataMap>
     */
    private function createMetadataMap(): ObjectProphecy
    {
        $metadataMap = $this->prophesize(MetadataMap::class);

        $fooBarMetadata = new RouteBasedResourceMetadata(
            TestAsset\FooBar::class,
            'foo-bar',
            self::getObjectPropertyHydratorClass()
        );

        $metadataMap->has(TestAsset\FooBar::class)->willReturn(true);
        $metadataMap->get(TestAsset\FooBar::class)->willReturn($fooBarMetadata);

        $childMetadata = new RouteBasedResourceMetadata(
            TestAsset\Child::class,
            'child',
            self::getObjectPropertyHydratorClass()
        );

        $metadataMap->has(TestAsset\Child::class)->willReturn(true);
        $metadataMap->get(TestAsset\Child::class)->willReturn($childMetadata);

        $collectionMetadata = new RouteBasedCollectionMetadata(
            TestAsset\Collection::class,
            'items',
            'collection'
        );

        $metadataMap->has(TestAsset\Collection::class)->willReturn(true);
        $metadataMap->get(TestAsset\Collection::class)->willReturn($collectionMetadata);

        return $metadataMap;
    }

    /**
     * @psalm-return ObjectProphecy<ContainerInterface>
     */
    private function createHydrators(): ObjectProphecy
    {
        $hydratorClass = self::getObjectPropertyHydratorClass();

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get($hydratorClass)->willReturn(new $hydratorClass());
        return $hydrators;
    }

    /**
     * @param ServerRequestInterface|ObjectProphecy $request
     * @psalm-param ServerRequestInterface&ObjectProphecy $request
     * @psalm-return ObjectProphecy<LinkGenerator>
     */
    public function createLinkGenerator($request): ObjectProphecy
    {
        $linkGenerator = $this->prophesize(LinkGenerator::class);

        $linkGenerator
            ->fromRoute(
                'self',
                $request->reveal(),
                'foo-bar',
                Argument::that(function (array $params) {
                    return array_key_exists('id', $params)
                        && $params['id'] === 101010;
                })
            )
            ->willReturn(new Link('self', '/api/foo-bar/1234'));

        for ($i = 1; $i < 11; $i += 1) {
            $linkGenerator
                ->fromRoute(
                    'self',
                    $request->reveal(),
                    'child',
                    Argument::that(function (array $params) use ($i) {
                        return array_key_exists('id', $params)
                            && $params['id'] === $i;
                    })
                )
                ->willReturn(new Link('self', '/api/child/' . $i));
        }

        $linkGenerator
            ->fromRoute(
                'self',
                $request->reveal(),
                'collection',
                [],
                Argument::type('array')
            )
            ->willReturn(new Link('self', '/api/collection'));

        return $linkGenerator;
    }
}
