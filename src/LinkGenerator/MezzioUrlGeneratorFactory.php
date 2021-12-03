<?php

declare(strict_types=1);

namespace Mezzio\Hal\LinkGenerator;

use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function sprintf;

class MezzioUrlGeneratorFactory
{
    /** @var string */
    private $urlHelperServiceName;

    /**
     * Allow serialization
     */
    public static function __set_state(array $data): self
    {
        return new self(
            $data['urlHelperServiceName'] ?? UrlHelper::class
        );
    }

    /**
     * Vary behavior based on the URL helper service name.
     */
    public function __construct(string $urlHelperServiceName = UrlHelper::class)
    {
        $this->urlHelperServiceName = $urlHelperServiceName;
    }

    public function __invoke(ContainerInterface $container): MezzioUrlGenerator
    {
        if (! $container->has($this->urlHelperServiceName)) {
            throw new RuntimeException(sprintf(
                '%s requires a %s in order to generate a %s instance; none found',
                self::class,
                $this->urlHelperServiceName,
                MezzioUrlGenerator::class
            ));
        }

        return new MezzioUrlGenerator(
            $container->get($this->urlHelperServiceName),
            $container->has(ServerUrlHelper::class)
                ? $container->get(ServerUrlHelper::class)
                : ($container->has(\Zend\Expressive\Helper\ServerUrlHelper::class)
                    ? $container->get(\Zend\Expressive\Helper\ServerUrlHelper::class)
                    : null)
        );
    }
}
