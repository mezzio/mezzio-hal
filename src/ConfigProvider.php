<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Mezzio\Hal\LinkGenerator\MezzioUrlGenerator;
use Mezzio\Hal\LinkGenerator\UrlGeneratorInterface;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadataFactory;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadataFactory;
use Mezzio\Hal\Metadata\UrlBasedCollectionMetadata;
use Mezzio\Hal\Metadata\UrlBasedCollectionMetadataFactory;
use Mezzio\Hal\Metadata\UrlBasedResourceMetadata;
use Mezzio\Hal\Metadata\UrlBasedResourceMetadataFactory;
use Mezzio\Hal\ResourceGenerator\RouteBasedCollectionStrategy;
use Mezzio\Hal\ResourceGenerator\RouteBasedResourceStrategy;
use Mezzio\Hal\ResourceGenerator\UrlBasedCollectionStrategy;
use Mezzio\Hal\ResourceGenerator\UrlBasedResourceStrategy;
use Zend\Expressive\Hal\LinkGenerator\ExpressiveUrlGenerator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'mezzio-hal'   => $this->getHalConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'    => [
                UrlGeneratorInterface::class      => MezzioUrlGenerator::class,
                ResourceGeneratorInterface::class => ResourceGenerator::class,

                // Legacy Zend Framework aliases
                \Zend\Expressive\Hal\LinkGenerator\UrlGeneratorInterface::class => UrlGeneratorInterface::class,
                \Zend\Expressive\Hal\HalResponseFactory::class                  => HalResponseFactory::class,
                \Zend\Expressive\Hal\LinkGenerator::class                       => LinkGenerator::class,
                ExpressiveUrlGenerator::class                                   => MezzioUrlGenerator::class,
                \Zend\Expressive\Hal\Metadata\MetadataMap::class                => MetadataMap::class,
                \Zend\Expressive\Hal\ResourceGenerator::class                   => ResourceGenerator::class,
                \Zend\Expressive\Hal\RouteBasedCollectionStrategy::class        => RouteBasedCollectionStrategy::class,
                \Zend\Expressive\Hal\RouteBasedResourceStrategy::class          => RouteBasedResourceStrategy::class,
                \Zend\Expressive\Hal\UrlBasedCollectionStrategy::class          => UrlBasedCollectionStrategy::class,
                \Zend\Expressive\Hal\UrlBasedResourceStrategy::class            => UrlBasedResourceStrategy::class,
            ],
            'factories'  => [
                HalResponseFactory::class => HalResponseFactoryFactory::class,
                LinkGenerator::class      => LinkGeneratorFactory::class,
                MezzioUrlGenerator::class => LinkGenerator\MezzioUrlGeneratorFactory::class,
                MetadataMap::class        => Metadata\MetadataMapFactory::class,
                ResourceGenerator::class  => ResourceGeneratorFactory::class,
            ],
            'invokables' => [
                RouteBasedCollectionStrategy::class => RouteBasedCollectionStrategy::class,
                RouteBasedResourceStrategy::class   => RouteBasedResourceStrategy::class,
                UrlBasedCollectionStrategy::class   => UrlBasedCollectionStrategy::class,
                UrlBasedResourceStrategy::class     => UrlBasedResourceStrategy::class,
            ],
        ];
    }

    public function getHalConfig(): array
    {
        return [
            'resource-generator' => [
                'strategies' => [ // The registered strategies and their metadata types
                    RouteBasedCollectionMetadata::class => RouteBasedCollectionStrategy::class,
                    RouteBasedResourceMetadata::class   => RouteBasedResourceStrategy::class,
                    UrlBasedCollectionMetadata::class   => UrlBasedCollectionStrategy::class,
                    UrlBasedResourceMetadata::class     => UrlBasedResourceStrategy::class,
                ],
            ],
            'metadata-factories' => [ // The factories for the metadata types
                RouteBasedCollectionMetadata::class => RouteBasedCollectionMetadataFactory::class,
                RouteBasedResourceMetadata::class   => RouteBasedResourceMetadataFactory::class,
                UrlBasedCollectionMetadata::class   => UrlBasedCollectionMetadataFactory::class,
                UrlBasedResourceMetadata::class     => UrlBasedResourceMetadataFactory::class,
            ],
        ];
    }
}
