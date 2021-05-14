<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Mezzio\Hal\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        self::assertIsArray($config);

        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('mezzio-hal', $config);
        self::assertIsArray($config['dependencies']);
    }
}
