<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ServerRequestInterface;

interface StrategyInterface
{
    /**
     * @param object $instance Instance from which to create HalResource.
     * @throws Exception\UnexpectedMetadataTypeException for metadata types the
     *     strategy cannot handle.
     */
    public function createResource(
        $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : HalResource;
}
