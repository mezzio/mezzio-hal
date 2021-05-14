<?php

namespace Mezzio\Hal\Renderer;

use Mezzio\Hal\HalResource;

interface RendererInterface
{
    public function render(HalResource $resource): string;
}
