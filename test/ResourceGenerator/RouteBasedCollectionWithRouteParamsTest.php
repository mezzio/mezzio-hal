<?php

declare(strict_types=1);

namespace MezzioTest\Hal\ResourceGenerator;

use ArrayObject;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function sprintf;

class RouteBasedCollectionWithRouteParamsTest extends TestCase
{
    use Assertions;

    use ProphecyTrait;

    public function testUsesRouteParamsAndQueriesWithPaginatorSpecifiedInMetadataWhenGeneratingLinkHref(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('p', 1)->willReturn(3);
        $request->getQueryParams()->willReturn([
            'query_1' => 'value_1',
        ]);

        $linkGenerator = $this->prophesize(LinkGenerator::class);
        $this->createLinkGeneratorProphecy($linkGenerator, $request, 'self', 3);
        $this->createLinkGeneratorProphecy($linkGenerator, $request, 'first', 1);
        $this->createLinkGeneratorProphecy($linkGenerator, $request, 'prev', 2);
        $this->createLinkGeneratorProphecy($linkGenerator, $request, 'next', 4);
        $this->createLinkGeneratorProphecy($linkGenerator, $request, 'last', 5);

        $metadataMap = $this->prophesize(MetadataMap::class);

        $resourceMetadata = new RouteBasedResourceMetadata(
            TestAsset\FooBar::class,
            'foo-bar',
            self::getObjectPropertyHydratorClass(),
            'id',
            ['foo_id' => 1234],
            ['id' => 'bar_id']
        );

        $metadataMap->has(TestAsset\FooBar::class)->willReturn(true);
        $metadataMap->get(TestAsset\FooBar::class)->willReturn($resourceMetadata);

        $collectionMetadata = new RouteBasedCollectionMetadata(
            Paginator::class,
            'foo-bar',
            'foo-bar',
            'p',
            RouteBasedCollectionMetadata::TYPE_PLACEHOLDER,
            ['foo_id' => 1234],
            ['sort' => 'ASC']
        );

        $metadataMap->has(Paginator::class)->willReturn(true);
        $metadataMap->get(Paginator::class)->willReturn($collectionMetadata);

        $hydratorClass = self::getObjectPropertyHydratorClass();

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get($hydratorClass)->willReturn(new $hydratorClass());

        $collection = new Paginator(new ArrayAdapter($this->createCollectionItems(
            $linkGenerator,
            $request
        )));
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
            RouteBasedCollectionMetadata::class,
            ResourceGenerator\RouteBasedCollectionStrategy::class
        );

        $resource = $generator->fromObject($collection, $request->reveal());

        $this->assertInstanceOf(HalResource::class, $resource);
        $self = $this->getLinkByRel('self', $resource);
        $this->assertLink('self', '/api/foo/1234/p/3?query_1=value_1&sort=ASC', $self);
        $first = $this->getLinkByRel('first', $resource);
        $this->assertLink('first', '/api/foo/1234/p/1?query_1=value_1&sort=ASC', $first);
        $prev = $this->getLinkByRel('prev', $resource);
        $this->assertLink('prev', '/api/foo/1234/p/2?query_1=value_1&sort=ASC', $prev);
        $next = $this->getLinkByRel('next', $resource);
        $this->assertLink('next', '/api/foo/1234/p/4?query_1=value_1&sort=ASC', $next);
        $last = $this->getLinkByRel('last', $resource);
        $this->assertLink('last', '/api/foo/1234/p/5?query_1=value_1&sort=ASC', $last);
    }

    public function testUsesRouteParamsAndQueriesSpecifiedInMetadataWhenGeneratingLinkHref(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('param_1', 1)->willReturn(3);
        $request->getQueryParams()->willReturn([
            'query_1' => 'value_1',
            'query_2' => 'value_2',
        ]);

        $metadataMap = $this->prophesize(MetadataMap::class);

        $resourceMetadata = new RouteBasedResourceMetadata(
            TestAsset\FooBar::class,
            'foo-bar',
            self::getObjectPropertyHydratorClass(),
            'id',
            ['foo_id' => 1234],
            ['id' => 'bar_id']
        );

        $metadataMap->has(TestAsset\FooBar::class)->willReturn(true);
        $metadataMap->get(TestAsset\FooBar::class)->willReturn($resourceMetadata);

        $collectionMetadata = new RouteBasedCollectionMetadata(
            ArrayObject::class,
            'foo-bar',
            'foo-bar',
            'p',
            RouteBasedCollectionMetadata::TYPE_PLACEHOLDER,
            [],
            ['query_2' => 'overridden_2']
        );
        $linkGenerator      = $this->prophesize(LinkGenerator::class);
        $linkGenerator->fromRoute(
            'self',
            $request->reveal(),
            'foo-bar',
            [],
            ['query_1' => 'value_1', 'query_2' => 'overridden_2']
        )->willReturn(new Link('self', '/api/foo/1234/p/3?query1=value_1&query_2=overridden_2'));

        $metadataMap->has(ArrayObject::class)->willReturn(true);
        $metadataMap->get(ArrayObject::class)->willReturn($collectionMetadata);

        $hydratorClass = self::getObjectPropertyHydratorClass();

        $hydrators = $this->prophesize(ContainerInterface::class);
        $hydrators->get($hydratorClass)->willReturn(new $hydratorClass());

        $collection = new ArrayObject($this->createCollectionItems(
            $linkGenerator,
            $request
        ));

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

        $resource = $generator->fromObject($collection, $request->reveal());

        $this->assertInstanceOf(HalResource::class, $resource);
        $self = $this->getLinkByRel('self', $resource);
        $this->assertLink('self', '/api/foo/1234/p/3?query1=value_1&query_2=overridden_2', $self);
    }

    /**
     * @param LinkGenerator|ObjectProphecy $linkGenerator
     * @param ServerRequestInterface|ObjectProphecy $request
     * @psalm-param LinkGenerator&ObjectProphecy $linkGenerator
     * @psalm-param ServerRequestInterface&ObjectProphecy $request
     */
    private function createLinkGeneratorProphecy($linkGenerator, $request, string $rel, int $page): void
    {
        $linkGenerator->fromRoute(
            $rel,
            $request->reveal(),
            'foo-bar',
            Argument::that(function (array $params) use ($page) {
                return array_key_exists('foo_id', $params)
                    && array_key_exists('p', $params)
                    && $params['foo_id'] === 1234
                    && $params['p'] === $page;
            }),
            [
                'query_1' => 'value_1',
                'sort'    => 'ASC',
            ]
        )->willReturn(new Link($rel, sprintf('/api/foo/1234/p/%d?query_1=value_1&sort=ASC', $page)));
    }

    /**
     * @param LinkGenerator|ObjectProphecy $linkGenerator
     * @param ServerRequestInterface|ObjectProphecy $request
     * @psalm-param LinkGenerator&ObjectProphecy $linkGenerator
     * @psalm-param ServerRequestInterface&ObjectProphecy $request
     */
    private function createCollectionItems($linkGenerator, $request): array
    {
        $instance      = new TestAsset\FooBar();
        $instance->foo = 'BAR';
        $instance->bar = 'BAZ';

        $items = [];
        for ($i = 1; $i < 15; $i += 1) {
            $next     = clone $instance;
            $next->id = $i;
            $items[]  = $next;

            $linkGenerator
                ->fromRoute(
                    'self',
                    $request->reveal(),
                    'foo-bar',
                    Argument::that(function (array $params) use ($i) {
                        return array_key_exists('foo_id', $params)
                            && array_key_exists('bar_id', $params)
                            && $params['foo_id'] === 1234
                            && $params['bar_id'] === $i;
                    })
                )
                ->willReturn(new Link('self', '/api/foo/1234/bar/' . $i));
        }
        return $items;
    }
}
