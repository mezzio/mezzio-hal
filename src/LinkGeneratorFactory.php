<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Psr\Container\ContainerInterface;

final readonly class LinkGeneratorFactory
{
    /**
     * Allow serialization
     */
    public static function __set_state(array $data): self
    {
        return new self(
            $data['urlGeneratorServiceName'] ?? LinkGenerator\UrlGeneratorInterface::class
        );
    }

    /**
     * Allow varying behavior based on URL generator service name.
     */
    public function __construct(
        private string $urlGeneratorServiceName = LinkGenerator\UrlGeneratorInterface::class
    ) {
    }

    public function __invoke(ContainerInterface $container): LinkGenerator
    {
        return new LinkGenerator(
            $container->get($this->urlGeneratorServiceName)
        );
    }
}
