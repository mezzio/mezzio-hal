<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Hal;

use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\Renderer;
use MezzioTest\Hal\Renderer\TestAsset;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

use function strstr;

class HalResponseFactoryTest extends TestCase
{
    use TestAsset;

    use ProphecyTrait;

    public function setUp(): void
    {
        $this->request      = $this->prophesize(ServerRequestInterface::class);
        $this->response     = $this->prophesize(ResponseInterface::class);
        $this->jsonRenderer = $this->prophesize(Renderer\JsonRenderer::class);
        $this->xmlRenderer  = $this->prophesize(Renderer\XmlRenderer::class);
        $this->factory      = new HalResponseFactory(
            function () {
                return $this->response->reveal();
            },
            $this->jsonRenderer->reveal(),
            $this->xmlRenderer->reveal()
        );
    }

    public function testReturnsJsonResponseIfNoAcceptHeaderPresent()
    {
        $resource = $this->createExampleResource();
        $this->jsonRenderer->render($resource)->willReturn('{}');
        $this->xmlRenderer->render($resource)->shouldNotBeCalled();
        $this->request->getHeaderLine('Accept')->willReturn('');

        $stream = $this->prophesize(StreamInterface::class);
        $stream->write('{}')->shouldBeCalled();
        $this->response->getBody()->will([$stream, 'reveal']);
        $this->response->withHeader('Content-Type', 'application/hal+json')->will([$this->response, 'reveal']);

        $response = $this->factory->createResponse(
            $this->request->reveal(),
            $resource
        );
        $this->assertSame($this->response->reveal(), $response);
    }

    public function jsonAcceptHeaders()
    {
        return [
            'application/json'             => ['application/json'],
            'application/hal+json'         => ['application/hal+json'],
            'application/vnd.example+json' => ['application/vnd.example+json'],
        ];
    }

    /**
     * @dataProvider jsonAcceptHeaders
     */
    public function testReturnsJsonResponseIfAcceptHeaderMatchesJson(string $header)
    {
        $resource = $this->createExampleResource();
        $this->jsonRenderer->render($resource)->willReturn('{}');
        $this->xmlRenderer->render($resource)->shouldNotBeCalled();
        $this->request->getHeaderLine('Accept')->willReturn($header);

        $stream = $this->prophesize(StreamInterface::class);
        $stream->write('{}')->shouldBeCalled();
        $this->response->getBody()->will([$stream, 'reveal']);
        $this->response->withHeader('Content-Type', 'application/hal+json')->will([$this->response, 'reveal']);

        $response = $this->factory->createResponse(
            $this->request->reveal(),
            $resource
        );
        $this->assertSame($this->response->reveal(), $response);
    }

    public function xmlAcceptHeaders()
    {
        return [
            'application/xml'             => ['application/xml'],
            'application/xhtml+xml'       => ['application/xhtml+xml'],
            'application/hal+xml'         => ['application/hal+xml'],
            'application/vnd.example+xml' => ['application/vnd.example+xml'],
        ];
    }

    /**
     * @dataProvider xmlAcceptHeaders
     */
    public function testReturnsXmlResponseIfAcceptHeaderMatchesXml(string $header)
    {
        $resource = $this->createExampleResource();
        $this->xmlRenderer->render($resource)->willReturn('<resource/>');
        $this->jsonRenderer->render($resource)->shouldNotBeCalled();
        $this->request->getHeaderLine('Accept')->willReturn($header);

        $stream = $this->prophesize(StreamInterface::class);
        $stream->write('<resource/>')->shouldBeCalled();
        $this->response->getBody()->will([$stream, 'reveal']);
        $this->response->withHeader('Content-Type', 'application/hal+xml')->will([$this->response, 'reveal']);

        $response = $this->factory->createResponse(
            $this->request->reveal(),
            $resource
        );
        $this->assertSame($this->response->reveal(), $response);
    }

    public function customMediaTypes()
    {
        // @codingStandardsIgnoreStart
        return [
            'json' => ['application/json', 'application/vnd.example', '{}', 'application/vnd.example+json'],
            'xml'  => ['application/xml',  'application/vnd.example', '<resource/>',  'application/vnd.example+xml'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider customMediaTypes
     */
    public function testUsesProvidedMediaTypeInReturnedResponseWithMatchedFormatAppended(
        string $header,
        string $mediaType,
        string $responseBody,
        string $expectedMediaType
    ) {
        $resource = $this->createExampleResource();
        switch (true) {
            case (strstr($header, 'json')):
                $this->jsonRenderer->render($resource)->willReturn($responseBody);
                $this->xmlRenderer->render($resource)->shouldNotBeCalled();
                break;
            case (strstr($header, 'xml')):
                $this->xmlRenderer->render($resource)->willReturn($responseBody);
                $this->jsonRenderer->render($resource)->shouldNotBeCalled();
                // fall-through
            default:
                break;
        }
        $this->request->getHeaderLine('Accept')->willReturn($header);

        $stream = $this->prophesize(StreamInterface::class);
        $stream->write($responseBody)->shouldBeCalled();
        $this->response->getBody()->will([$stream, 'reveal']);
        $this->response->withHeader('Content-Type', $expectedMediaType)->will([$this->response, 'reveal']);

        $response = $this->factory->createResponse(
            $this->request->reveal(),
            $resource,
            $mediaType
        );
        $this->assertSame($this->response->reveal(), $response);
    }
}
