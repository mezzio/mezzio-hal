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
    /** @var string */
    private $urlGeneratorServiceName;

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
    public function __construct(string $urlGeneratorServiceName = LinkGenerator\UrlGeneratorInterface::class)
    {
        $this->urlGeneratorServiceName = $urlGeneratorServiceName;
    }

    public function __invoke(ContainerInterface $container): LinkGenerator
    {
        return new LinkGenerator(
            $container->get($this->urlGeneratorServiceName)
        );
    }
}
