<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface;

class LinkGeneratorTest extends TestCase
{
    use ProphecyTrait;

    public function testUsesComposedUrlGeneratorToGenerateHrefForLink(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

        $urlGenerator = $this->prophesize(LinkGenerator\UrlGeneratorInterface::class);
        $urlGenerator->generate(
            $request,
            'test',
            ['library' => 'laminas'],
            ['sort' => 'asc']
        )->willReturn('/library/test?sort=asc');

        $linkGenerator = new LinkGenerator($urlGenerator->reveal());

        $link = $linkGenerator->fromRoute(
            'library',
            $request,
            'test',
            ['library' => 'laminas'],
            ['sort' => 'asc'],
            ['type' => 'https://example.com/doc/library']
        );

        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame('/library/test?sort=asc', $link->getHref());
        $this->assertSame(['library'], $link->getRels());
        $this->assertSame(['type' => 'https://example.com/doc/library'], $link->getAttributes());
        $this->assertFalse($link->isTemplated());
    }

    public function testUsesComposedUrlGeneratorToGenerateHrefForTemplatedLink(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

        $urlGenerator = $this->prophesize(LinkGenerator\UrlGeneratorInterface::class);
        $urlGenerator->generate(
            $request,
            'test',
            ['library' => 'laminas'],
            ['sort' => 'asc']
        )->willReturn('/library/test?sort=asc');

        $linkGenerator = new LinkGenerator($urlGenerator->reveal());

        $link = $linkGenerator->templatedFromRoute(
            'library',
            $request,
            'test',
            ['library' => 'laminas'],
            ['sort' => 'asc'],
            ['type' => 'https://example.com/doc/library']
        );

        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame('/library/test?sort=asc', $link->getHref());
        $this->assertSame(['library'], $link->getRels());
        $this->assertSame(['type' => 'https://example.com/doc/library'], $link->getAttributes());
        $this->assertTrue($link->isTemplated());
    }
}
