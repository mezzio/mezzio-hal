<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\Renderer;
use MezzioTest\Hal\Renderer\TestAsset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

use function strlen;
use function strstr;

class HalResponseFactoryTest extends TestCase
{
    use TestAsset;

    /** @var ServerRequestInterface&MockObject */
    private $request;

    /** @var ResponseInterface&MockObject */
    private $response;

    /** @var Renderer\JsonRenderer&MockObject */
    private $jsonRenderer;

    /** @var Renderer\XmlRenderer&MockObject */
    private $xmlRenderer;

    /** @var HalResponseFactory */
    private $factory;

    public function setUp(): void
    {
        $this->request  = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->response->method('withStatus')->willReturnSelf();
        $this->jsonRenderer = $this->createMock(Renderer\JsonRenderer::class);
        $this->xmlRenderer  = $this->createMock(Renderer\XmlRenderer::class);
        $this->factory      = new HalResponseFactory(
            function (): ResponseInterface {
                return $this->response;
            },
            $this->jsonRenderer,
            $this->xmlRenderer
        );
    }

    public function testReturnsJsonResponseIfNoAcceptHeaderPresent(): void
    {
        $resource = $this->createExampleResource();
        $this->jsonRenderer
            ->method('render')
            ->with($resource)
            ->willReturn('{}');

        $this->xmlRenderer
            ->expects(self::never())
            ->method('render');

        $this->request
            ->method('getHeaderLine')
            ->with('Accept')
            ->willReturn('');

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::once())
            ->method('write')
            ->with('{}')
            ->willReturn(2);

        $this->response
            ->method('getBody')
            ->willReturn($stream);

        $this->response
            ->method('withHeader')
            ->with('Content-Type', 'application/hal+json')
            ->willReturnSelf();

        $response = $this->factory->createResponse(
            $this->request,
            $resource
        );

        self::assertSame($this->response, $response);
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public function jsonAcceptHeaders(): array
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
    public function testReturnsJsonResponseIfAcceptHeaderMatchesJson(string $header): void
    {
        $resource = $this->createExampleResource();
        $this->jsonRenderer
            ->method('render')
            ->with($resource)
            ->willReturn('{}');

        $this->xmlRenderer
            ->expects(self::never())
            ->method('render');

        $this->request
            ->method('getHeaderLine')
            ->with('Accept')
            ->willReturn($header);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::once())
            ->method('write')
            ->with('{}')
            ->willReturn(2);

        $this->response
            ->method('getBody')
            ->willReturn($stream);

        $this->response
            ->expects(self::once())
            ->method('withHeader')
            ->with('Content-Type', 'application/hal+json')
            ->willReturnSelf();

        $response = $this->factory->createResponse(
            $this->request,
            $resource
        );
        self::assertSame($this->response, $response);
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public function xmlAcceptHeaders(): array
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
    public function testReturnsXmlResponseIfAcceptHeaderMatchesXml(string $header): void
    {
        $resource = $this->createExampleResource();
        $this->xmlRenderer
            ->method('render')
            ->with($resource)
            ->willReturn('<resource/>');

        $this->jsonRenderer
            ->expects(self::never())
            ->method('render');

        $this->request
            ->method('getHeaderLine')
            ->with('Accept')
            ->willReturn($header);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::once())
            ->method('write')
            ->with('<resource/>')
            ->willReturn(11);

        $this->response
            ->method('getBody')
            ->wilLReturn($stream);

        $this->response
            ->expects(self::once())
            ->method('withHeader')
            ->with('Content-Type', 'application/hal+xml')
            ->willReturnSelf();

        $response = $this->factory->createResponse(
            $this->request,
            $resource
        );
        self::assertSame($this->response, $response);
    }

    /**
     * @psalm-return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public function customMediaTypes(): array
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
    ): void {
        $resource = $this->createExampleResource();
        switch (true) {
            case strstr($header, 'json'):
                $this->jsonRenderer
                    ->expects(self::once())
                    ->method('render')
                    ->with($resource)
                    ->willReturn($responseBody);
                $this->xmlRenderer
                    ->expects(self::never())
                    ->method('render');
                break;
            case strstr($header, 'xml') !== false:
                $this->xmlRenderer
                    ->expects(self::once())
                    ->method('render')
                    ->with($resource)
                    ->willReturn($responseBody);
                $this->jsonRenderer
                    ->expects(self::never())
                    ->method('render');
                break;
            default:
                break;
        }
        $this->request
            ->method('getHeaderLine')
            ->with('Accept')
            ->willReturn($header);

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->expects(self::once())
            ->method('write')
            ->with($responseBody)
            ->willReturn(strlen($responseBody));

        $this->response
            ->method('getBody')
            ->willReturn($stream);

        $this->response
            ->expects(self::once())
            ->method('withHeader')
            ->with('Content-Type', $expectedMediaType)
            ->willReturnSelf();

        $response = $this->factory->createResponse(
            $this->request,
            $resource,
            $mediaType
        );
        self::assertSame($this->response, $response);
    }
}
