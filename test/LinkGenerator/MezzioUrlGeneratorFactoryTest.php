<?php

declare(strict_types=1);

namespace MezzioTest\Hal\LinkGenerator;

use Mezzio\Hal\LinkGenerator\MezzioUrlGeneratorFactory;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class MezzioUrlGeneratorFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfUrlHelperIsMissingFromContainer(): void
    {
        $this->container
            ->expects(self::once())
            ->method('has')
            ->withConsecutive([UrlHelper::class])
            ->willReturn(false);

        $this->container
            ->expects(self::never())
            ->method('get');

        $factory = new MezzioUrlGeneratorFactory();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(UrlHelper::class);
        $factory($this->container);
    }

    public function testFactoryCanCreateUrlGeneratorWithOnlyUrlHelperPresentInContainer(): void
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $request   = $this->createMock(ServerRequestInterface::class);

        $this->container
            ->expects(self::exactly(3))
            ->method('has')
            ->withConsecutive(
                [UrlHelper::class],
                [ServerUrlHelper::class],
                [\Zend\Expressive\Helper\ServerUrlHelper::class]
            )
            ->willReturnOnConsecutiveCalls(true, false, false);
        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(UrlHelper::class)
            ->willReturn($urlHelper);

        $urlHelper
            ->expects(self::once())
            ->method('generate')
            ->with('foobar', [], [])
            ->willReturn('/baz');

        $factory   = new MezzioUrlGeneratorFactory();
        $generator = $factory($this->container);

        self::assertSame('/baz', $generator->generate($request, 'foobar'));
    }

    public function testFactoryCanCreateUrlGeneratorWithBothUrlHelperAndServerUrlHelper(): void
    {
        $urlHelper       = $this->createMock(UrlHelper::class);
        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);
        $request         = $this->createMock(ServerRequestInterface::class);
        $uri             = $this->createMock(UriInterface::class);

        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(
                [UrlHelper::class],
                [ServerUrlHelper::class]
            )->willReturn(true);

        $this->container
            ->expects(self::exactly(2))
            ->method('get')
            ->withConsecutive(
                [UrlHelper::class],
                [ServerUrlHelper::class]
            )->willReturnOnConsecutiveCalls($urlHelper, $serverUrlHelper);

        $urlHelper
            ->expects(self::once())
            ->method('generate')
            ->with('foobar', [], [])
            ->willReturn('/baz');

        $serverUrlHelper
            ->expects(self::once())
            ->method('setUri')
            ->with($uri);

        $serverUrlHelper
            ->expects(self::once())
            ->method('__invoke')
            ->with('/baz')
            ->willReturn('https://example.com/baz');

        $request
            ->expects(self::once())
            ->method('getUri')
            ->willReturn($uri);

        $factory   = new MezzioUrlGeneratorFactory();
        $generator = $factory($this->container);

        self::assertSame('https://example.com/baz', $generator->generate($request, 'foobar'));
    }

    public function testFactoryCanAcceptUrlHelperServiceNameToConstructor(): void
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $request   = $this->createMock(ServerRequestInterface::class);

        $this->container
            ->expects(self::exactly(3))
            ->method('has')
            ->withConsecutive(
                [CustomUrlHelper::class],
                [ServerUrlHelper::class],
                [\Zend\Expressive\Helper\ServerUrlHelper::class]
            )->willReturnOnConsecutiveCalls(true, false, false);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(CustomUrlHelper::class)
            ->willReturn($urlHelper);

        $urlHelper
            ->expects(self::once())
            ->method('generate')
            ->with('foobar', [], [])
            ->willReturn('/baz');

        $factory   = new MezzioUrlGeneratorFactory(CustomUrlHelper::class);
        $generator = $factory($this->container);

        self::assertSame('/baz', $generator->generate($request, 'foobar'));
    }

    public function testFactoryIsSerializable(): void
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $request   = $this->createMock(ServerRequestInterface::class);

        $this->container
            ->expects(self::exactly(3))
            ->method('has')
            ->withConsecutive(
                ['customUrlHelper'],
                [ServerUrlHelper::class],
                [\Zend\Expressive\Helper\ServerUrlHelper::class]
            )->willReturnOnConsecutiveCalls(true, false, false);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('customUrlHelper')
            ->willReturn($urlHelper);

        $urlHelper
            ->expects(self::once())
            ->method('generate')
            ->with('route-from-custom-helper', [], [])
            ->willReturn('/custom');

        $factory = MezzioUrlGeneratorFactory::__set_state([
            'urlHelperServiceName' => 'customUrlHelper',
        ]);

        $generator = $factory($this->container);

        self::assertSame('/custom', $generator->generate($request, 'route-from-custom-helper'));
    }
}
