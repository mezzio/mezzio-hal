<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'aliases' => [
                LinkGenerator\UrlGenerator::class => LinkGenerator\MezzioUrlGenerator::class,

                // Legacy Zend Framework aliases
                \Zend\Expressive\Hal\LinkGenerator\UrlGenerator::class => LinkGenerator\UrlGenerator::class,
                \Zend\Expressive\Hal\HalResponseFactory::class => HalResponseFactory::class,
                \Zend\Expressive\Hal\LinkGenerator::class => LinkGenerator::class,
                \Zend\Expressive\Hal\LinkGenerator\ExpressiveUrlGenerator::class => LinkGenerator\MezzioUrlGenerator::class,
                \Zend\Expressive\Hal\Metadata\MetadataMap::class => Metadata\MetadataMap::class,
                \Zend\Expressive\Hal\ResourceGenerator::class => ResourceGenerator::class,
            ],
            'factories' => [
                HalResponseFactory::class => HalResponseFactoryFactory::class,
                LinkGenerator::class => LinkGeneratorFactory::class,
                LinkGenerator\MezzioUrlGenerator::class => LinkGenerator\MezzioUrlGeneratorFactory::class,
                Metadata\MetadataMap::class => Metadata\MetadataMapFactory::class,
                ResourceGenerator::class => ResourceGeneratorFactory::class,
            ],
        ];
    }
}
