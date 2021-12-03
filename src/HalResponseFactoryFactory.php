<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Hal\Renderer\JsonRenderer;
use Zend\Expressive\Hal\Renderer\XmlRenderer;

/**
 * Create and return a HalResponseFactory instance.
 *
 * Utilizes the following services:
 *
 * - `Psr\Http\Message\ResponseInterface`; must resolve to a PHP callable capable
 *   of producing an instance of that type.
 * - `Hal\Renderer\JsonRenderer`, if present; otherwise, creates an instance.
 * - `Hal\Renderer\XmlRenderer`, if present; otherwise, creates an instance.
 */
class HalResponseFactoryFactory
{
    /**
     * @throws RuntimeException If neither a ResponseInterface service is
     *     present nor laminas-diactoros is installed.
     */
    public function __invoke(ContainerInterface $container): HalResponseFactory
    {
        $jsonRenderer = $container->has(Renderer\JsonRenderer::class)
            ? $container->get(Renderer\JsonRenderer::class)
            : ($container->has(JsonRenderer::class)
                ? $container->get(JsonRenderer::class)
                : new Renderer\JsonRenderer());

        $xmlRenderer = $container->has(Renderer\XmlRenderer::class)
            ? $container->get(Renderer\XmlRenderer::class)
            : ($container->has(XmlRenderer::class)
                ? $container->get(XmlRenderer::class)
                : new Renderer\XmlRenderer());

        return new HalResponseFactory(
            $container->get(ResponseInterface::class),
            $jsonRenderer,
            $xmlRenderer
        );
    }
}
