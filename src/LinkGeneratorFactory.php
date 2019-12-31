<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal;

use Psr\Container\ContainerInterface;

class LinkGeneratorFactory
{
    public function __invoke(ContainerInterface $container) : LinkGenerator
    {
        return new LinkGenerator(
            $container->get(LinkGenerator\UrlGenerator::class)
        );
    }
}
