<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResourceGeneratorInterface
{
    public function getHydrators(): ContainerInterface;

    public function getMetadataMap(): Metadata\MetadataMap;

    public function getLinkGenerator(): LinkGenerator;

    public function fromArray(array $data, ?string $uri = null): HalResource;

    public function fromObject(object $instance, ServerRequestInterface $request, int $depth = 0): HalResource;
}
