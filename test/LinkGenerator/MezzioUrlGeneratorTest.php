<?php

declare(strict_types=1);

namespace MezzioTest\Hal\LinkGenerator;

use Mezzio\Hal\LinkGenerator\MezzioUrlGenerator;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use MezzioTest\Hal\PHPUnitDeprecatedAssertions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class MezzioUrlGeneratorTest extends TestCase
{
    use PHPUnitDeprecatedAssertions;

    use ProphecyTrait;

    public function testCanGenerateUrlWithOnlyUrlHelper(): void
    {
        $urlHelper = $this->prophesize(UrlHelper::class);
        $urlHelper->generate('test', ['foo' => 'bar'], ['baz' => 'bat'])->willReturn('/test/bar?baz=bat');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getUri()->shouldNotBeCalled();

        $generator = new MezzioUrlGenerator($urlHelper->reveal());

        $this->assertSame('/test/bar?baz=bat', $generator->generate(
            $request->reveal(),
            'test',
            ['foo' => 'bar'],
            ['baz' => 'bat']
        ));
    }

    public function testCanGenerateFullyQualifiedURIWhenServerUrlHelperIsComposed(): void
    {
        $uri = $this->prophesize(UriInterface::class);
        $uri->withQuery('')->will([$uri, 'reveal']);
        $uri->withFragment('')->will([$uri, 'reveal'])->shouldBeCalledTimes(1);
        $uri->getPath()->willReturn('/some/path');
        $uri->withPath('/test/bar')->will([$uri, 'reveal']);
        $uri->withQuery('baz=bat')->will([$uri, 'reveal']);

        $uri
            ->withFragment(Argument::that(function ($fragment) {
                return ! empty($fragment);
            }))
            ->shouldNotBeCalled();

        $uri->__toString()->willReturn('https://api.example.com/test/bar?baz=bat');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getUri()->will([$uri, 'reveal']);

        $urlHelper = $this->prophesize(UrlHelper::class);
        $urlHelper->generate('test', ['foo' => 'bar'], ['baz' => 'bat'])->willReturn('/test/bar?baz=bat');

        $serverUrlHelper = new ServerUrlHelper();

        $generator = new MezzioUrlGenerator($urlHelper->reveal(), $serverUrlHelper);

        $this->assertSame('https://api.example.com/test/bar?baz=bat', $generator->generate(
            $request->reveal(),
            'test',
            ['foo' => 'bar'],
            ['baz' => 'bat']
        ));

        // The helper should be cloned on each invocation, ensuring that the URI
        // is not persisted.
        $this->assertAttributeEmpty('uri', $serverUrlHelper);
    }
}
