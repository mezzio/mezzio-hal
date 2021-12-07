<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\LinkGeneratorFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class LinkGeneratorFactoryTest extends TestCase
{
    use PHPUnitDeprecatedAssertions;

    use ProphecyTrait;

    public function testReturnsLinkGeneratorInstance(): void
    {
        $urlGenerator = $this->createMock(LinkGenerator\UrlGeneratorInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(LinkGenerator\UrlGeneratorInterface::class)->willReturn($urlGenerator);

        $instance = (new LinkGeneratorFactory())($container->reveal());
        self::assertInstanceOf(LinkGenerator::class, $instance);
        self::assertAttributeSame($urlGenerator, 'urlGenerator', $instance);
    }

    public function testConstructorAllowsSpecifyingUrlGeneratorServiceName(): void
    {
        $urlGenerator = $this->createMock(LinkGenerator\UrlGeneratorInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(UrlGenerator::class)->willReturn($urlGenerator);

        $instance = (new LinkGeneratorFactory(UrlGenerator::class))($container->reveal());
        self::assertInstanceOf(LinkGenerator::class, $instance);
        self::assertAttributeSame($urlGenerator, 'urlGenerator', $instance);
    }

    public function testFactoryIsSerializable(): void
    {
        $factory = LinkGeneratorFactory::__set_state([
            'urlGeneratorServiceName' => UrlGenerator::class,
        ]);

        $this->assertInstanceOf(LinkGeneratorFactory::class, $factory);
        $this->assertAttributeSame(UrlGenerator::class, 'urlGeneratorServiceName', $factory);
    }
}
