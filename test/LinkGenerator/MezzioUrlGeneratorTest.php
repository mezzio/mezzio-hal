<?php

declare(strict_types=1);

namespace MezzioTest\Hal\LinkGenerator;

use Mezzio\Hal\LinkGenerator\MezzioUrlGenerator;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ReflectionProperty;

class MezzioUrlGeneratorTest extends TestCase
{
    public function testCanGenerateUrlWithOnlyUrlHelper(): void
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $urlHelper
            ->method('generate')
            ->with('test', ['foo' => 'bar'], ['baz' => 'bat'])
            ->willReturn('/test/bar?baz=bat');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::never())
            ->method('getUri');

        $generator = new MezzioUrlGenerator($urlHelper);

        self::assertSame('/test/bar?baz=bat', $generator->generate(
            $request,
            'test',
            ['foo' => 'bar'],
            ['baz' => 'bat']
        ));
    }

    public function testCanGenerateFullyQualifiedURIWhenServerUrlHelperIsComposed(): void
    {
        $uri = $this->createMock(UriInterface::class);

        $uri
            ->expects(self::once())
            ->method('withFragment')
            ->with('')
            ->willReturnSelf();

        $uri
            ->method('getPath')
            ->willReturn('/some/path');

        $uri
            ->method('withPath')
            ->with('/test/bar')
            ->willReturnSelf();

        $uri
            ->expects(self::exactly(2))
            ->method('withQuery')
            ->withConsecutive([''], ['baz=bat'])
            ->willReturnSelf();

        $uri
            ->method('__toString')
            ->willReturn('https://api.example.com/test/bar?baz=bat');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $urlHelper = $this->createMock(UrlHelper::class);

        $urlHelper
            ->method('generate')
            ->with('test', ['foo' => 'bar'], ['baz' => 'bat'])
            ->willReturn('/test/bar?baz=bat');

        $serverUrlHelper = new ServerUrlHelper();

        $generator = new MezzioUrlGenerator($urlHelper, $serverUrlHelper);

        self::assertSame('https://api.example.com/test/bar?baz=bat', $generator->generate(
            $request,
            'test',
            ['foo' => 'bar'],
            ['baz' => 'bat']
        ));

        // The helper should be cloned on each invocation, ensuring that the URI
        // is not persisted.
        $reflectionProperty = new ReflectionProperty($serverUrlHelper, 'uri');
        self::assertNull($reflectionProperty->getValue($serverUrlHelper));
    }
}
