<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\Metadata\AbstractCollectionMetadata;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This trait is intended to correct the difference in method signature
 * from the trait linked below, and their implementations, since that difference
 * now results in a fatal error in PHP 8.
 *
 * @see ExtractCollectionTrait::generateSelfLink
 */
trait GenerateSelfLinkTrait
{
    abstract protected function generateSelfLink(
        AbstractCollectionMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    );
}
