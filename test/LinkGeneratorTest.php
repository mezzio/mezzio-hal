<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Hal;

use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class LinkGeneratorTest extends TestCase
{
    public function testUsesComposedUrlGeneratorToGenerateHrefForLink()
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

    public function testUsesComposedUrlGeneratorToGenerateHrefForTemplatedLink()
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
