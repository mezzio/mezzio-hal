<?php

declare(strict_types=1);

namespace Mezzio\Hal\LinkGenerator;

use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function sprintf;

final class MezzioUrlGeneratorFactory
{
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
    public function __construct(private readonly string $urlHelperServiceName = UrlHelper::class)
    {
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

        $serverUrlHelper = null;
        if ($container->has(ServerUrlHelper::class)) {
            /** @var ServerUrlHelper $serverUrlHelper */
            $serverUrlHelper = $container->get(ServerUrlHelper::class);
        }

        return new MezzioUrlGenerator(
            $container->get($this->urlHelperServiceName),
            $serverUrlHelper
        );
    }
}
