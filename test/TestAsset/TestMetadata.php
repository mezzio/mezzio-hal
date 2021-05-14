<?php

namespace MezzioTest\Hal\TestAsset;

use Mezzio\Hal\Metadata\AbstractMetadata;
use stdClass;

class TestMetadata extends AbstractMetadata
{
    public function getClass(): string
    {
        return stdClass::class;
    }
}
