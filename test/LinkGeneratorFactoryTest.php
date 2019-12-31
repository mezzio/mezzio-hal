<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Hal;

use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\LinkGeneratorFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class LinkGeneratorFactoryTest extends TestCase
{
    public function testReturnsLinkGeneratorInstance() : void
    {
        $urlGenerator = $this->prophesize(LinkGenerator\UrlGeneratorInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(LinkGenerator\UrlGeneratorInterface::class)->willReturn($urlGenerator);

        $instance = (new LinkGeneratorFactory())($container->reveal());
        self::assertInstanceOf(LinkGenerator::class, $instance);
        self::assertAttributeSame($urlGenerator, 'urlGenerator', $instance);
    }

    public function testConstructorAllowsSpecifyingUrlGeneratorServiceName()
    {
        $urlGenerator = $this->prophesize(LinkGenerator\UrlGeneratorInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(UrlGenerator::class)->willReturn($urlGenerator);

        $instance = (new LinkGeneratorFactory(UrlGenerator::class))($container->reveal());
        self::assertInstanceOf(LinkGenerator::class, $instance);
        self::assertAttributeSame($urlGenerator, 'urlGenerator', $instance);
    }

    public function testFactoryIsSerializable()
    {
        $factory = LinkGeneratorFactory::__set_state([
            'urlGeneratorServiceName' => UrlGenerator::class,
        ]);

        $this->assertInstanceOf(LinkGeneratorFactory::class, $factory);
        $this->assertAttributeSame(UrlGenerator::class, 'urlGeneratorServiceName', $factory);
    }
}
