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
use MezzioTest\Hal\TestAsset;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResourceWithNestedInstancesTest extends TestCase
{
    use Assertions;

    public function testNestedObjectInMetadataMapIsEmbeddedAsResource()
    {
        $child = new TestAsset\Child;
        $child->id = 9876;
        $child->message = 'ack';

        $parent = new TestAsset\FooBar;
        $parent->id = 1234;
        $parent->foo = 'FOO';
        $parent->bar = $child;

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

        $resource = $generator->fromObject($parent, $request->reveal());
        $this->assertInstanceOf(HalResource::class, $resource);

        $childResource = $resource->getElement('bar');
        $this->assertInstanceOf(HalResource::class, $childResource);
        $this->assertEquals($child->id, $childResource->getElement('id'));
        $this->assertEquals($child->message, $childResource->getElement('message'));
    }

    public function createMetadataMap()
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

        return $metadataMap;
    }

    public function createLinkGenerator($request)
    {
        $linkGenerator = $this->prophesize(LinkGenerator::class);

        $linkGenerator
            ->fromRoute(
                'self',
                $request->reveal(),
                'foo-bar',
                [ 'id' => 1234 ]
            )
            ->willReturn(new Link('self', '/api/foo-bar/1234'));

        $linkGenerator
            ->fromRoute(
                'self',
                $request->reveal(),
                'child',
                [ 'id' => 9876 ]
            )
            ->willReturn(new Link('self', '/api/child/9876'));

        return $linkGenerator;
    }

    public function createHydrators()
    {
        $hydratorClass = self::getObjectPropertyHydratorClass();

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get($hydratorClass)->willReturn(new $hydratorClass());
        return $hydrators;
    }
}
