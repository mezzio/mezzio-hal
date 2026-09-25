<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Exception', 'src/Exception')
    ->layer('Link', [
        'src/Link.php',
        'src/LinkCollection.php',
    ])
    ->layer('HalResource', 'src/HalResource.php')
    ->layer('LinkGenerator', [
        'src/LinkGenerator.php',
        'src/LinkGeneratorFactory.php',
        'src/LinkGenerator',
    ])
    ->layer('MetadataException', 'src/Metadata/Exception')
    ->layer('Metadata', 'src/Metadata', 'src/Metadata/Exception')
    ->layer('Renderer', 'src/Renderer')
    ->layer('Response', [
        'src/HalResponseFactory.php',
        'src/HalResponseFactoryFactory.php',
        'src/Psr17ResponseFactoryTrait.php',
        'src/Response',
    ])
    ->layer('ResourceGeneratorException', 'src/ResourceGenerator/Exception')
    ->layer('ResourceGenerator', [
        'src/ResourceGenerator.php',
        'src/ResourceGeneratorFactory.php',
        'src/ResourceGeneratorInterface.php',
        'src/ResourceGenerator',
    ], 'src/ResourceGenerator/Exception')
    ->layer('ConfigProvider', 'src/ConfigProvider.php')
    ->ruleset([
        'Exception'                  => ['HalResource', 'Metadata', 'ResourceGenerator'],
        'Link'                       => [],
        'HalResource'                => ['Exception', 'Link'],
        'LinkGenerator'              => ['Link'],
        'MetadataException'          => ['Exception', 'Metadata'],
        'Metadata'                   => ['Link', 'MetadataException'],
        'Renderer'                   => ['+HalResource'],
        'Response'                   => ['+Renderer'],
        'ResourceGeneratorException' => ['Exception', 'Metadata', 'ResourceGenerator'],
        'ResourceGenerator'          => ['+HalResource', '+LinkGenerator', '+Metadata', 'ResourceGeneratorException'],
        'ConfigProvider'             => ['+ResourceGenerator', '+Response'],
    ]);
