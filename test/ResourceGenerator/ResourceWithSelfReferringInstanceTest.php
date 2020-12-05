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
use MezzioTest\Hal\TestAsset;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ResourceWithSelfReferringInstanceTest extends TestCase
{
    use Assertions;

    public function testSelfReferringIsEmbeddedAsResource(): void
    {
        $parent = new TestAsset\FooBar;
        $parent->id = 1234;
        $parent->foo = 'FOO';
        $parent->bar = $parent;

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

        $childResource = $resource->getElement('bar');
        self::assertInstanceOf(HalResource::class, $childResource);
        self::assertCount(0, $childResource->getElements());
    }

    /**
     * @return MetadataMap|ObjectProphecy
     */
    public function createMetadataMap()
    {
        $metadataMap = $this->prophesize(MetadataMap::class);

        $fooBarMetadata = new RouteBasedResourceMetadata(
            TestAsset\FooBar::class,
            'foo-bar',
            self::getObjectPropertyHydratorClass(),
            'id',
            'id',
            [],
            0
        );

        $metadataMap->has(TestAsset\FooBar::class)->willReturn(true);
        $metadataMap->get(TestAsset\FooBar::class)->willReturn($fooBarMetadata);

        return $metadataMap;
    }

    /**
     * @param ObjectProphecy $request
     *
     * @return LinkGenerator|ObjectProphecy
     */
    public function createLinkGenerator(ObjectProphecy $request)
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

        return $linkGenerator;
    }

    /**
     * @return ObjectProphecy|ContainerInterface
     */
    public function createHydrators()
    {
        $hydratorClass = self::getObjectPropertyHydratorClass();

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get($hydratorClass)->willReturn(new $hydratorClass());
        return $hydrators;
    }
}
