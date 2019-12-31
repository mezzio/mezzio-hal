<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Hal\Renderer;

use Mezzio\Hal\Renderer\JsonRenderer;
use PHPUnit\Framework\TestCase;

class JsonRendererTest extends TestCase
{
    use TestAsset;

    public function testDelegatesToJsonEncode()
    {
        $renderer = new JsonRenderer();
        $resource = $this->createExampleResource();
        $expected = json_encode($resource, JsonRenderer::DEFAULT_JSON_FLAGS);

        $this->assertEquals($expected, $renderer->render($resource));
    }

    public function testRendersUsingJsonFlagsProvidedToConstructor()
    {
        $jsonFlags = 0;
        $renderer  = new JsonRenderer($jsonFlags);
        $resource  = $this->createExampleResource();
        $expected  = json_encode($resource, $jsonFlags);

        $this->assertEquals($expected, $renderer->render($resource));
    }
}
