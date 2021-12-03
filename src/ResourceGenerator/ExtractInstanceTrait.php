<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator;

use Laminas\Hydrator\ExtractionInterface;
use Mezzio\Hal\Metadata\AbstractCollectionMetadata;
use Mezzio\Hal\Metadata\AbstractMetadata;
use Mezzio\Hal\ResourceGeneratorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;

use function get_class;
use function is_object;

trait ExtractInstanceTrait
{
    /**
     * @throws ContainerExceptionInterface If the extractor service cannot be retrieved.
     */
    private function extractInstance(
        object $instance,
        AbstractMetadata $metadata,
        ResourceGeneratorInterface $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ): array {
        $hydrators = $resourceGenerator->getHydrators();
        $extractor = $hydrators->get($metadata->getExtractor());
        if (! $extractor instanceof ExtractionInterface) {
            throw Exception\InvalidExtractorException::fromInstance($extractor);
        }

        $array = $extractor->extract($instance);

        if ($metadata->hasReachedMaxDepth($depth)) {
            return $array;
        }

        // Extract nested resources if present in metadata map
        $metadataMap = $resourceGenerator->getMetadataMap();
        foreach ($array as $key => $value) {
            if (! is_object($value)) {
                continue;
            }

            $childClass = get_class($value);
            if (! $metadataMap->has($childClass)) {
                continue;
            }

            $childData = $resourceGenerator->fromObject($value, $request, $depth + 1);

            // Nested collections need to be merged.
            $childMetadata = $metadataMap->get($childClass);
            if ($childMetadata instanceof AbstractCollectionMetadata) {
                $childData = $childData->getElement($childMetadata->getCollectionRelation());
            }

            $array[$key] = $childData;
        }

        return $array;
    }
}
