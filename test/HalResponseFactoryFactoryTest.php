<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\HalResponseFactoryFactory;
use Mezzio\Hal\Renderer;
use Mezzio\Hal\Response\CallableResponseFactoryDecorator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use Zend\Expressive\Hal\Renderer\JsonRenderer;
use Zend\Expressive\Hal\Renderer\XmlRenderer;

class HalResponseFactoryFactoryTest extends TestCase
{
    use PHPUnitDeprecatedAssertions;

    use ProphecyTrait;

    public static function assertResponseFactoryReturns(ResponseInterface $expected, HalResponseFactory $factory): void
    {
        $r = new ReflectionProperty($factory, 'responseFactory');
        $r->setAccessible(true);
        $responseFactory = $r->getValue($factory);

        self::assertInstanceOf(CallableResponseFactoryDecorator::class, $responseFactory);
        self::assertSame($expected, $responseFactory->getResponseFromCallable());
    }

    public function testReturnsHalResponseFactoryInstance(): void
    {
        $jsonRenderer    = $this->createMock(Renderer\JsonRenderer::class);
        $xmlRenderer     = $this->createMock(Renderer\XmlRenderer::class);
        $response        = $this->createMock(ResponseInterface::class);
        $responseFactory = function () use ($response): ResponseInterface {
            return $response;
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseFactoryInterface::class)->willReturn(false);
        $container->get(ResponseInterface::class)->willReturn($responseFactory);
        $container->has(Renderer\JsonRenderer::class)->willReturn(true);
        $container->get(Renderer\JsonRenderer::class)->willReturn($jsonRenderer);
        $container->has(Renderer\XmlRenderer::class)->willReturn(true);
        $container->get(Renderer\XmlRenderer::class)->willReturn($xmlRenderer);

        $instance = (new HalResponseFactoryFactory())($container->reveal());
        self::assertAttributeSame($jsonRenderer, 'jsonRenderer', $instance);
        self::assertAttributeSame($xmlRenderer, 'xmlRenderer', $instance);
        self::assertResponseFactoryReturns($response, $instance);
    }

    public function testReturnsHalResponseFactoryInstanceWithoutConfiguredDependencies(): void
    {
        $response        = $this->createMock(ResponseInterface::class);
        $responseFactory = function () use ($response): ResponseInterface {
            return $response;
        };
        $container       = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseFactoryInterface::class)->willReturn(false);
        $container->get(ResponseInterface::class)->willReturn($responseFactory);
        $container->has(Renderer\JsonRenderer::class)->willReturn(false);
        $container->has(JsonRenderer::class)->willReturn(false);
        $container->has(Renderer\XmlRenderer::class)->willReturn(false);
        $container->has(XmlRenderer::class)->willReturn(false);

        $instance = (new HalResponseFactoryFactory())($container->reveal());
        self::assertAttributeInstanceOf(Renderer\JsonRenderer::class, 'jsonRenderer', $instance);
        self::assertAttributeInstanceOf(Renderer\XmlRenderer::class, 'xmlRenderer', $instance);
        self::assertResponseFactoryReturns($response, $instance);
    }

    public function testReturnsHalResponseFactoryInstanceWhenResponseInterfaceReturnsFactory(): void
    {
        $jsonRenderer    = $this->createMock(Renderer\JsonRenderer::class);
        $xmlRenderer     = $this->createMock(Renderer\XmlRenderer::class);
        $response        = $this->createMock(ResponseInterface::class);
        $responseFactory = function () use ($response): ResponseInterface {
            return $response;
        };
        $stream          = new class ()
        {
            public function __invoke()
            {
            }
        };

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseFactoryInterface::class)->willReturn(false);
        $container->has(Renderer\JsonRenderer::class)->willReturn(true);
        $container->get(Renderer\JsonRenderer::class)->willReturn($jsonRenderer);
        $container->has(Renderer\XmlRenderer::class)->willReturn(true);
        $container->get(Renderer\XmlRenderer::class)->willReturn($xmlRenderer);
        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($responseFactory);
        $container->has(StreamInterface::class)->willReturn(true);
        $container->get(StreamInterface::class)->willReturn($stream);

        $instance = (new HalResponseFactoryFactory())($container->reveal());
        self::assertAttributeSame($jsonRenderer, 'jsonRenderer', $instance);
        self::assertAttributeSame($xmlRenderer, 'xmlRenderer', $instance);
    }
}
