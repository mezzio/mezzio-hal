<?php

declare(strict_types=1);

namespace MezzioTest\Hal\Renderer;

use Mezzio\Hal\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;

use function json_encode;

class JsonRendererTest extends TestCase
{
    use TestAsset;

    public function testDelegatesToJsonEncode(): void
    {
        $renderer = new JsonRenderer();
        $resource = $this->createExampleResource();
        $expected = json_encode($resource, JsonRenderer::DEFAULT_JSON_FLAGS);

        $this->assertEquals($expected, $renderer->render($resource));
    }

    public function testRendersUsingJsonFlagsProvidedToConstructor(): void
    {
        $jsonFlags = 0;
        $renderer  = new JsonRenderer($jsonFlags);
        $resource  = $this->createExampleResource();
        $expected  = json_encode($resource, $jsonFlags);

        $this->assertEquals($expected, $renderer->render($resource));
    }
}
