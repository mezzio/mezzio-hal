<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

use Mezzio\Hal\Metadata\MetadataMapFactory;

final class TestMetadataMapFactory extends MetadataMapFactory
{
    public function createTestMetadata(): TestMetadata
    {
        return new TestMetadata();
    }
}
