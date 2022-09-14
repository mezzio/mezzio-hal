<?php

declare(strict_types=1);

namespace MezzioTest\Hal\ResourceGenerator;

use ArrayIterator;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Mezzio\Hal\ResourceGenerator\RouteBasedCollectionStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function array_map;
use function count;
use function range;

class DoctrinePaginatorTest extends TestCase
{
    /** @var RouteBasedCollectionMetadata&MockObject */
    private $metadata;

    /** @var LinkGenerator&MockObject */
    private $linkGenerator;

    /** @var ResourceGenerator&MockObject */
    private $generator;

    /** @var ServerRequestInterface&MockObject */
    private $request;

    /** @var Paginator&MockObject */
    private $paginator;

    /** @var RouteBasedCollectionStrategy */
    private $strategy;

    public function setUp(): void
    {
        $this->metadata      = $this->createMock(RouteBasedCollectionMetadata::class);
        $this->linkGenerator = $this->createMock(LinkGenerator::class);
        $this->generator     = $this->createMock(ResourceGenerator::class);
        $this->request       = $this->createMock(ServerRequestInterface::class);
        $this->paginator     = $this->createMock(Paginator::class);

        $this->strategy = new RouteBasedCollectionStrategy();
    }

    /**
     * @psalm-return AbstractQuery&MockObject
     */
    public function mockQuery(): AbstractQuery
    {
        return $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMaxResults', 'setFirstResult'])
            ->getMockForAbstractClass();
    }

    public function invalidPageCombinations(): iterable
    {
        yield 'negative'   => [-1, 100];
        yield 'zero'       => [0, 100];
        yield 'too-high'   => [2, 1];
    }

    /**
     * @dataProvider invalidPageCombinations
     */
    public function testThrowsOutOfBoundsExceptionForInvalidPage(int $page, int $numPages): void
    {
        $query = $this->mockQuery();
        $query
            ->expects($this->once())
            ->method('getMaxResults')
            ->with()
            ->willReturn(15);

        $this->paginator
            ->method('getQuery')
            ->willReturn($query);

        $this->paginator
            ->method('count')
            ->willReturn($numPages);

        $this->metadata
            ->method('getPaginationParamType')
            ->willReturn(RouteBasedCollectionMetadata::TYPE_QUERY);

        $this->metadata
            ->method('getPaginationParam')
            ->willReturn('page_num');

        $this->request
            ->method('getQueryParams')
            ->willReturn(['page_num' => $page]);

        $this->expectException(OutOfBoundsException::class);
        $this->strategy->createResource(
            $this->paginator,
            $this->metadata,
            $this->generator,
            $this->request
        );
    }

    public function testDoesNotCreateLinksForUnknownPaginationParamType(): void
    {
        $query = $this->mockQuery();
        $query->expects($this->once())
            ->method('getMaxResults')
            ->with()
            ->willReturn(15);
        $this->paginator
            ->method('getQuery')
            ->willReturn($query);

        $this->paginator
            ->method('count')
            ->willReturn(100);

        $this->metadata
            ->method('getPaginationParamType')
            ->willReturn('unknown');

        $this->metadata
            ->expects(self::never())
            ->method('getPaginationParam');

        $this->metadata
            ->method('getRouteParams')
            ->willReturn([]);

        $this->metadata
            ->method('getQueryStringArguments')
            ->willReturn([]);

        $this->metadata
            ->method('getRoute')
            ->willReturn('test');

        $this->metadata
            ->method('getCollectionRelation')
            ->willReturn('test');

        $this->request
            ->expects(self::once())
            ->method('getQueryParams')
            ->willReturn(['page' => 3]);

        $this->request
            ->expects(self::never())
            ->method('getAttribute');

        $values = array_map(function ($value) {
            return (object) ['value' => $value];
        }, range(46, 60));
        $this->paginator
            ->method('getIterator')
            ->willReturn(new ArrayIterator($values));

        $consecutiveGeneratorArguments = [];
        foreach (range(46, 60) as $value) {
            $consecutiveGeneratorArguments[] = [
                (object) ['value' => $value],
                $this->request,
                1,
            ];
        }

        $this->generator
            ->expects(self::exactly(count($consecutiveGeneratorArguments)))
            ->method('fromObject')
            ->withConsecutive(
                ...$consecutiveGeneratorArguments
            )
            ->willReturnCallback(function (): HalResource {
                $resource = $this->createMock(HalResource::class);
                $resource
                    ->method('getElements')
                    ->willReturn(['test' => true]);

                return $resource;
            });

        $this->generator
            ->method('getLinkGenerator')
            ->willReturn($this->linkGenerator);

        $link = $this->createMock(Link::class);
        $this->linkGenerator
            ->method('fromRoute')
            ->with(
                'self',
                $this->request,
                'test',
                [],
                ['page' => 3]
            )
            ->willReturn($link);

        $this->strategy->createResource(
            $this->paginator,
            $this->metadata,
            $this->generator,
            $this->request
        );
    }

    public function testCreatesLinksForQueryBasedPagination(): void
    {
        $query = $this->mockQuery();
        $query
            ->expects($this->once())
            ->method('getMaxResults')
            ->with()
            ->willReturn(15);
        $query
            ->expects($this->once())
            ->method('setFirstResult')
            ->with(30);

        $this->paginator
            ->method('getQuery')
            ->willReturn($query);

        $this->paginator
            ->method('count')
            ->willReturn(100);

        $this->metadata
            ->method('getPaginationParamType')
            ->willReturn(RouteBasedCollectionMetadata::TYPE_QUERY);

        $this->metadata
            ->method('getPaginationParam')
            ->willReturn('page_num');

        $this->metadata
            ->method('getRouteParams')
            ->willReturn([]);

        $this->metadata
            ->method('getQueryStringArguments')
            ->willReturn([]);

        $this->metadata
            ->method('getRoute')
            ->willReturn('test');

        $this->metadata
            ->method('getCollectionRelation')
            ->willReturn('test');

        $this->request
            ->expects(self::once())
            ->method('getQueryParams')
            ->willReturn(['page_num' => 3]);

        $this->request
            ->expects(self::never())
            ->method('getAttribute');

        $values = array_map(function ($value) {
            return (object) ['value' => $value];
        }, range(46, 60));

        $this->paginator
            ->method('getIterator')
            ->willReturn(new ArrayIterator($values));

        $testCase                      = $this;
        $consecutiveGeneratorArguments = [];
        foreach (range(46, 60) as $value) {
            $consecutiveGeneratorArguments[] = [
                (object) ['value' => $value],
                $this->request,
                1,
            ];
        }

        $this->generator
            ->expects(self::exactly(count($consecutiveGeneratorArguments)))
            ->method('fromObject')
            ->withConsecutive(
                ...$consecutiveGeneratorArguments
            )
            ->willReturnCallback(function () use ($testCase): HalResource {
                $resource = $testCase->createMock(HalResource::class);
                $resource->method('getElements')->willReturn(['test' => true]);
                return $resource;
            });

        $this->generator
            ->method('getLinkGenerator')
            ->willReturn($this->linkGenerator);

        $paginationLinks = [
            'self'  => ['page_num' => 3],
            'first' => ['page_num' => 1],
            'prev'  => ['page_num' => 2],
            'next'  => ['page_num' => 4],
            'last'  => ['page_num' => 7],
        ];

        $consecutiveLinkGenerationArguments = [];
        foreach ($paginationLinks as $relation => $queryStringArgs) {
            $consecutiveLinkGenerationArguments[] = [
                $relation,
                $this->request,
                'test',
                [],
                $queryStringArgs,
            ];
        }

        $link = $this->createMock(Link::class);
        $this->linkGenerator
            ->expects(self::exactly(count($consecutiveLinkGenerationArguments)))
            ->method('fromRoute')
            ->withConsecutive(...$consecutiveLinkGenerationArguments)
            ->willReturn($link);

        $resource = $this->strategy->createResource(
            $this->paginator,
            $this->metadata,
            $this->generator,
            $this->request
        );

        self::assertInstanceOf(HalResource::class, $resource);
    }

    public function testCreatesLinksForRouteBasedPagination(): void
    {
        $query = $this->mockQuery();
        $query
            ->expects($this->once())
            ->method('getMaxResults')
            ->with()
            ->willReturn(15);

        $query
            ->expects($this->once())
            ->method('setFirstResult')
            ->with(30);
        $this->paginator
            ->method('getQuery')
            ->willReturn($query);
        $this->paginator->method('count')->willReturn(100);

        $this->metadata
            ->method('getPaginationParamType')
            ->willReturn(RouteBasedCollectionMetadata::TYPE_PLACEHOLDER);

        $this->metadata
            ->method('getPaginationParam')
            ->willReturn('page_num');

        $this->metadata
            ->method('getRouteParams')
            ->willReturn([]);

        $this->metadata
            ->method('getQueryStringArguments')
            ->willReturn([]);

        $this->metadata
            ->method('getRoute')
            ->willReturn('test');

        $this->metadata
            ->method('getCollectionRelation')
            ->willReturn('test');

        $this->request
            ->expects(self::never())
            ->method('getQueryParams');

        $this->request
            ->expects(self::once())
            ->method('getAttribute')
            ->with('page_num', 1)
            ->willReturn(3);

        $values = array_map(function ($value) {
            return (object) ['value' => $value];
        }, range(46, 60));

        $this->paginator
            ->method('getIterator')
            ->willReturn(new ArrayIterator($values));

        $testCase = $this;

        $consecutiveGeneratorArguments = [];
        foreach (range(46, 60) as $value) {
            $consecutiveGeneratorArguments[] = [
                (object) ['value' => $value],
                $this->request,
                1,
            ];
        }
        $this->generator
            ->expects(self::exactly(count($consecutiveGeneratorArguments)))
            ->method('fromObject')
            ->withConsecutive(...$consecutiveGeneratorArguments)
            ->willReturnCallback(function () use ($testCase): HalResource {
                $resource = $testCase->createMock(HalResource::class);
                $resource->method('getElements')->willReturn(['test' => true]);
                return $resource;
            });

        $this->generator
            ->method('getLinkGenerator')
            ->willReturn($this->linkGenerator);

        $paginationLinks = [
            'self'  => ['page_num' => 3],
            'first' => ['page_num' => 1],
            'prev'  => ['page_num' => 2],
            'next'  => ['page_num' => 4],
            'last'  => ['page_num' => 7],
        ];

        $consecutiveLinkGenerationArguments = [];
        foreach ($paginationLinks as $relation => $routeParams) {
            $consecutiveLinkGenerationArguments[] = [
                $relation,
                $this->request,
                'test',
                $routeParams,
                [],
            ];
        }

        $link = $this->createMock(Link::class);
        $this->linkGenerator
            ->method('fromRoute')
            ->withConsecutive(
                ...$consecutiveLinkGenerationArguments
            )
            ->willReturn($link);

        $resource = $this->strategy->createResource(
            $this->paginator,
            $this->metadata,
            $this->generator,
            $this->request
        );

        self::assertInstanceOf(HalResource::class, $resource);
    }
}
