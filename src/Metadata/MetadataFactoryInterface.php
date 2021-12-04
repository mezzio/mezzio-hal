<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

/**
 * Interface describing factories that create metadata instances.
 */
interface MetadataFactoryInterface
{
    /**
     * Creates a Metadata based on the MetadataMap configuration.
     *
     * @param string $requestedName The requested name of the metadata type
     * @param array  $metadata      The metadata should have the following structure:
     *     <code>
     *     [
     *         '__class__' => 'Fully qualified class name of an AbstractMetadata type',
     *         // additional key/value pairs as required by the metadata type.
     *     ]
     *     </code>
     *
     *     The '__class__' key decides which AbstractMetadata should be used
     *     (and which corresponding factory will be called to create it).
     */
    public function createMetadata(string $requestedName, array $metadata): AbstractMetadata;
}
