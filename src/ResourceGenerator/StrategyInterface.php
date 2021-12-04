<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

interface StrategyInterface
{
    /**
     * @param object $instance Instance from which to create HalResource.
     * @throws Exception\UnexpectedMetadataTypeException For metadata types the
     *     strategy cannot handle.
     */
    public function createResource(
        object $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ): HalResource;
}
