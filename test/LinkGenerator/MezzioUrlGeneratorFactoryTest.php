<?php

declare(strict_types=1);

namespace MezzioTest\Hal\LinkGenerator;

use Mezzio\Hal\LinkGenerator\MezzioUrlGeneratorFactory;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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

        $factory   = new MezzioUrlGeneratorFactory();
        $generator = $factory($this->container);

        self::assertSame($urlHelper, $generator->getUrlHelper());
    }

    public function testFactoryCanCreateUrlGeneratorWithBothUrlHelperAndServerUrlHelper(): void
    {
        $urlHelper       = $this->createMock(UrlHelper::class);
        $serverUrlHelper = $this->createMock(ServerUrlHelper::class);

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

        $factory   = new MezzioUrlGeneratorFactory();
        $generator = $factory($this->container);

        self::assertSame($urlHelper, $generator->getUrlHelper());
        self::assertSame($serverUrlHelper, $generator->getServerUrlHelper());
    }

    public function testFactoryCanAcceptUrlHelperServiceNameToConstructor(): void
    {
        $urlHelper = $this->createMock(UrlHelper::class);

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

        $factory   = new MezzioUrlGeneratorFactory(CustomUrlHelper::class);
        $generator = $factory($this->container);

        self::assertSame($urlHelper, $generator->getUrlHelper());
        self::assertNull($generator->getServerUrlHelper());
    }

    public function testFactoryIsSerializable(): void
    {
        $factory = MezzioUrlGeneratorFactory::__set_state([
            'urlHelperServiceName' => 'customUrlHelper',
        ]);

        self::assertSame('customUrlHelper', $factory->getUrlHelperServiceName());
    }
}
