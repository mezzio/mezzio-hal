<?php

namespace MezzioTest\Hal\TestAsset;

use Mezzio\Hal\Metadata\MetadataMapFactory;

class TestMetadataMapFactory extends MetadataMapFactory
{
    protected function createTestMetadata(array $metadata): TestMetadata
    {
        return new TestMetadata();
    }
}
