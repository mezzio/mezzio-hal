<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Hal\LinkGenerator;

use Mezzio\Hal\LinkGenerator\MezzioUrlGenerator;
use Mezzio\Hal\LinkGenerator\MezzioUrlGeneratorFactory;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

class MezzioUrlGeneratorFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfUrlHelperIsMissingFromContainer()
    {
        $this->container->has(UrlHelper::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Helper\UrlHelper::class)->willReturn(false);
        $this->container->get(UrlHelper::class)->shouldNotBeCalled();
        $this->container->get(\Zend\Expressive\Helper\UrlHelper::class)->shouldNotBeCalled();
        $this->container->has(ServerUrlHelper::class)->shouldNotBeCalled();
        $this->container->has(\Zend\Expressive\Helper\ServerUrlHelper::class)->shouldNotBeCalled();

        $factory = new MezzioUrlGeneratorFactory();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(UrlHelper::class);
        $factory($this->container->reveal());
    }

    public function testFactoryCanCreateUrlGeneratorWithOnlyUrlHelperPresentInContainer()
    {
        $urlHelper = $this->prophesize(UrlHelper::class)->reveal();

        $this->container->has(UrlHelper::class)->willReturn(true);
        $this->container->get(UrlHelper::class)->willReturn($urlHelper);
        $this->container->has(ServerUrlHelper::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Helper\ServerUrlHelper::class)->willReturn(false);
        $this->container->get(ServerUrlHelper::class)->shouldNotBeCalled();
        $this->container->get(\Zend\Expressive\Helper\ServerUrlHelper::class)->shouldNotBeCalled();

        $factory = new MezzioUrlGeneratorFactory();
        $generator = $factory($this->container->reveal());

        $this->assertInstanceOf(MezzioUrlGenerator::class, $generator);
        $this->assertAttributeSame($urlHelper, 'urlHelper', $generator);
    }

    public function testFactoryCanCreateUrlGeneratorWithBothUrlHelperAndServerUrlHelper()
    {
        $urlHelper = $this->prophesize(UrlHelper::class)->reveal();
        $serverUrlHelper = $this->prophesize(ServerUrlHelper::class)->reveal();

        $this->container->has(UrlHelper::class)->willReturn(true);
        $this->container->get(UrlHelper::class)->willReturn($urlHelper);
        $this->container->has(ServerUrlHelper::class)->willReturn(true);
        $this->container->get(ServerUrlHelper::class)->willReturn($serverUrlHelper);

        $factory = new MezzioUrlGeneratorFactory();
        $generator = $factory($this->container->reveal());

        $this->assertInstanceOf(MezzioUrlGenerator::class, $generator);
        $this->assertAttributeSame($urlHelper, 'urlHelper', $generator);
        $this->assertAttributeSame($serverUrlHelper, 'serverUrlHelper', $generator);
    }
}
