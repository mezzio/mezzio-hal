<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

use Mezzio\Hal\Psr17ResponseFactoryTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class Psr17ResponseFactoryTraitImplementation
{
    use Psr17ResponseFactoryTrait;

    public function __invoke(ContainerInterface $container): ResponseFactoryInterface
    {
        return $this->detectResponseFactory($container);
    }
}
