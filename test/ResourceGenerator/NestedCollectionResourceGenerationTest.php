<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

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
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_shift;

class NestedCollectionResourceGenerationTest extends TestCase
{
    use Assertions;

    use PHPUnitDeprecatedAssertions;

    use ProphecyTrait;

    public function testNestedCollectionIsEmbeddedAsAnArrayNotAHalCollection()
    {
        $collection = $this->createCollection();
        $foo = new TestAsset\FooBar;
        $foo->id = 101010;
        $foo->foo = 'foo';
        $foo->children = $collection;

        $request = $this->prophesize(ServerRequestInterface::class);
        $metadataMap = $this->createMetadataMap();
        $hydrators = $this->createHydrators();
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

    private function createCollection() : TestAsset\Collection
    {
        $items = [];
        for ($i = 1; $i < 11; $i += 1) {
            $item = new TestAsset\Child;
            $item->id = $i;
            $item->message = 'ack';
            $items[] = $item;
        }
        return new TestAsset\Collection($items);
    }

    private function createMetadataMap()
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

    private function createHydrators()
    {
        $hydratorClass = self::getObjectPropertyHydratorClass();

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get($hydratorClass)->willReturn(new $hydratorClass());
        return $hydrators;
    }

    public function createLinkGenerator($request)
    {
        $linkGenerator = $this->prophesize(LinkGenerator::class);

        $linkGenerator
            ->fromRoute(
                'self',
                $request->reveal(),
                'foo-bar',
                [ 'id' => 101010 ]
            )
            ->willReturn(new Link('self', '/api/foo-bar/1234'));

        for ($i = 1; $i < 11; $i += 1) {
            $linkGenerator
                ->fromRoute(
                    'self',
                    $request->reveal(),
                    'child',
                    [ 'id' => $i ]
                )
                ->willReturn(new Link('self', '/api/child/' . $i));
        }

        $linkGenerator
            ->fromRoute(
                'self',
                $request->reveal(),
                'collection',
                [],
                []
            )
            ->willReturn(new Link('self', '/api/collection'));

        return $linkGenerator;
    }
}
