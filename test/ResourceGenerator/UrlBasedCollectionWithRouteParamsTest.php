<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Hal\ResourceGenerator;

use Laminas\Hydrator\ObjectProperty as ObjectPropertyHydrator;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Hal\Metadata\UrlBasedCollectionMetadata;
use Mezzio\Hal\ResourceGenerator;
use MezzioTest\Hal\Assertions;
use MezzioTest\Hal\TestAsset;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlBasedCollectionWithRouteParamsTest extends TestCase
{
    use Assertions;

    public function testUsesQueriesWithPaginatorSpecifiedInMetadataWhenGeneratingLinkHref()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getQueryParams()->willReturn([
            'query_1' => 'value_1',
            'p' => 3,
            'sort' => 'ASC',
        ]);

        $linkGenerator = $this->prophesize(LinkGenerator::class);

        $metadataMap = $this->prophesize(MetadataMap::class);

        $resourceMetadata = new RouteBasedResourceMetadata(
            TestAsset\FooBar::class,
            'foo-bar',
            ObjectPropertyHydrator::class,
            'id',
            'bar_id',
            ['foo_id' => 1234]
        );

        $metadataMap->has(TestAsset\FooBar::class)->willReturn(true);
        $metadataMap->get(TestAsset\FooBar::class)->willReturn($resourceMetadata);

        $collectionMetadata = new UrlBasedCollectionMetadata(
            Paginator::class,
            'foo-bar',
            'http://test.local/collection/',
            'p',
            'query'
        );

        $metadataMap->has(Paginator::class)->willReturn(true);
        $metadataMap->get(Paginator::class)->willReturn($collectionMetadata);

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get(ObjectPropertyHydrator::class)->willReturn(new ObjectPropertyHydrator());

        $collection = new Paginator(new ArrayAdapter($this->createCollectionItems($linkGenerator, $request)));
        $collection->setItemCountPerPage(3);

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
            UrlBasedCollectionMetadata::class,
            ResourceGenerator\UrlBasedCollectionStrategy::class
        );

        $resource = $generator->fromObject($collection, $request->reveal());

        $this->assertInstanceOf(HalResource::class, $resource);
        $self = $this->getLinkByRel('self', $resource);
        $this->assertLink('self', 'http://test.local/collection/?query_1=value_1&p=3&sort=ASC', $self);
        $first = $this->getLinkByRel('first', $resource);
        $this->assertLink('first', 'http://test.local/collection/?query_1=value_1&p=1&sort=ASC', $first);
        $prev = $this->getLinkByRel('prev', $resource);
        $this->assertLink('prev', 'http://test.local/collection/?query_1=value_1&p=2&sort=ASC', $prev);
        $next = $this->getLinkByRel('next', $resource);
        $this->assertLink('next', 'http://test.local/collection/?query_1=value_1&p=4&sort=ASC', $next);
        $last = $this->getLinkByRel('last', $resource);
        $this->assertLink('last', 'http://test.local/collection/?query_1=value_1&p=5&sort=ASC', $last);
    }

    public function testUsesQueriesSpecifiedInMetadataWhenGeneratingLinkHref()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getQueryParams()->willReturn([
            'query_1' => 'value_1',
            'query_2' => 'value_2',
        ]);

        $metadataMap = $this->prophesize(MetadataMap::class);

        $resourceMetadata = new RouteBasedResourceMetadata(
            TestAsset\FooBar::class,
            'foo-bar',
            ObjectPropertyHydrator::class,
            'id',
            'bar_id',
            ['foo_id' => 1234]
        );

        $metadataMap->has(TestAsset\FooBar::class)->willReturn(true);
        $metadataMap->get(TestAsset\FooBar::class)->willReturn($resourceMetadata);

        $collectionMetadata = new UrlBasedCollectionMetadata(
            \ArrayObject::class,
            'foo-bar',
            'http://test.local/collection/',
            'p',
            'query'
        );
        $linkGenerator = $this->prophesize(LinkGenerator::class);

        $metadataMap->has(\ArrayObject::class)->willReturn(true);
        $metadataMap->get(\ArrayObject::class)->willReturn($collectionMetadata);

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get(ObjectPropertyHydrator::class)->willReturn(new ObjectPropertyHydrator());

        $collection = new \ArrayObject();

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
            UrlBasedCollectionMetadata::class,
            ResourceGenerator\UrlBasedCollectionStrategy::class
        );

        $resource = $generator->fromObject($collection, $request->reveal());

        $this->assertInstanceOf(HalResource::class, $resource);
        $self = $this->getLinkByRel('self', $resource);
        $this->assertLink('self', 'http://test.local/collection/?query_1=value_1&query_2=value_2', $self);
    }

    private function createCollectionItems($linkGenerator, $request) : array
    {
        $instance      = new TestAsset\FooBar;
        $instance->foo = 'BAR';
        $instance->bar = 'BAZ';

        $items = [];
        for ($i = 1; $i < 15; $i += 1) {
            $next = clone $instance;
            $next->id = $i;
            $items[] = $next;

            $linkGenerator
                ->fromRoute(
                    'self',
                    $request->reveal(),
                    'foo-bar',
                    [
                        'foo_id' => 1234,
                        'bar_id' => $i,
                    ]
                )
                ->willReturn(new Link('self', '/api/foo/1234/bar/' . $i));
        }
        return $items;
    }
}
