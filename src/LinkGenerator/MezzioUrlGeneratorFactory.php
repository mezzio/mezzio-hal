<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\LinkGenerator;

use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function sprintf;

class MezzioUrlGeneratorFactory
{
    public function __invoke(ContainerInterface $container) : MezzioUrlGenerator
    {
        if (! $container->has(UrlHelper::class)
            && ! $container->has(\Zend\Expressive\Helper\UrlHelper::class)
        ) {
            throw new RuntimeException(sprintf(
                '%s requires a %s in order to generate a %s instance; none found',
                __CLASS__,
                UrlHelper::class,
                MezzioUrlGenerator::class
            ));
        }

        return new MezzioUrlGenerator(
            $container->has(UrlHelper::class) ? $container->get(UrlHelper::class) : $container->get(\Zend\Expressive\Helper\UrlHelper::class),
            $container->has(ServerUrlHelper::class)
                ? $container->get(ServerUrlHelper::class)
                : ($container->has(\Zend\Expressive\Helper\ServerUrlHelper::class)
                    ? $container->get(\Zend\Expressive\Helper\ServerUrlHelper::class)
                    : null)
        );
    }
}
