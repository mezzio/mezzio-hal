<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use InvalidArgumentException;
use Mezzio\Hal\Link;
use MezzioTest\Hal\TestAsset\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Link\EvolvableLinkInterface;

class LinkTest extends TestCase
{
    public function testCanConstructLinkWithRelation(): void
    {
        $link = new Link('self');
        $this->assertInstanceOf(Link::class, $link);
        $this->assertInstanceOf(EvolvableLinkInterface::class, $link);
        $this->assertEquals(['self'], $link->getRels());
        $this->assertEquals('', $link->getHref());
        $this->assertFalse($link->isTemplated());
        $this->assertEquals([], $link->getAttributes());
    }

    public function testCanConstructLinkWithZeroStringRelation(): void
    {
        $link = new Link('0');

        $this->assertEquals(['0'], $link->getRels());
    }

    public function testCanNotConstructLinkWithEmptyString(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('$relation argument must be a non empty string or array of strings; received ')
        );

        new Link('');
    }

    public function testCanConstructLinkWithRelationAndUri(): void
    {
        $link = new Link('self', 'https://example.com/api/link');
        $this->assertEquals(['self'], $link->getRels());
        $this->assertEquals('https://example.com/api/link', $link->getHref());
    }

    public function testCanConstructLinkWithRelationAndTemplatedFlag(): void
    {
        $link = new Link('self', '', true);
        $this->assertEquals(['self'], $link->getRels());
        $this->assertTrue($link->isTemplated());
    }

    public function testCanConstructLinkWithRelationAndAttributes(): void
    {
        $link = new Link('self', '', false, ['foo' => 'bar']);
        $this->assertEquals(['self'], $link->getRels());
        $this->assertEquals(['foo' => 'bar'], $link->getAttributes());
    }

    public function testCanConstructFullyPopulatedLink(): void
    {
        $link = new Link(
            ['self', 'link'],
            'https://example.com/api/link{/id}',
            true,
            ['foo' => 'bar']
        );
        $this->assertEquals(['self', 'link'], $link->getRels());
        $this->assertEquals('https://example.com/api/link{/id}', $link->getHref());
        $this->assertTrue($link->isTemplated());
        $this->assertEquals(['foo' => 'bar'], $link->getAttributes());
    }

    public function testWithRelReturnsSameInstanceIfRelationIsAlreadyPresent(): void
    {
        $link = new Link('self');
        $new  = $link->withRel('self');
        $this->assertSame($link, $new);
    }

    public function testWithRelReturnsNewInstanceIfRelationIsNotAlreadyPresent(): void
    {
        $link = new Link('self');
        $new  = $link->withRel('link');
        $this->assertNotSame($link, $new);
        $this->assertEquals(['self'], $link->getRels());
        $this->assertEquals(['self', 'link'], $new->getRels());
    }

    public function testWithoutRelReturnsSameInstanceIfRelationIsNotPresent(): void
    {
        $link = new Link('self');
        $new  = $link->withoutRel('link');
        $this->assertSame($link, $new);
    }

    public function testWithoutRelReturnsNewInstanceIfRelationCanBeRemoved(): void
    {
        $link = new Link(['self', 'link']);
        $new  = $link->withoutRel('link');
        $this->assertNotSame($link, $new);
        $this->assertEquals(['self', 'link'], $link->getRels());
        $this->assertEquals(['self'], $new->getRels());
    }

    /**
     * @psalm-return iterable<string, array{0: string|Uri}>
     */
    public function validUriTypes(): iterable
    {
        yield 'string' => ['https://example.com/api/link'];
        yield 'castable-object' => [new Uri('https://example.com/api/link')];
    }

    /**
     * @dataProvider validUriTypes
     */
    public function testWithHrefReturnsNewInstanceWhenUriIsValid(string|Uri $uri): void
    {
        $link = new Link('self', 'https://example.com');
        $new  = $link->withHref($uri);
        $this->assertNotSame($link, $new);

        $stringHref = (string) $uri;
        $this->assertNotEquals($stringHref, $link->getHref());
        $this->assertEquals($stringHref, $new->getHref());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidAttributeValues(): array
    {
        return [
            'array-with-non-string-values' => [[null, false, true, 0, 0.0, 1, 1.1, 'foo']],
            'object'                       => [(object) ['name' => 'attribute']],
        ];
    }

    /**
     * @dataProvider invalidAttributeValues
     */
    public function testWithAttributeRaisesExceptionForInvalidAttributeValue(mixed $value): void
    {
        $link = new Link('self');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$value');
        $link->withAttribute('foo', $value);
    }

    /**
     * @psalm-return array<string, array{0: string, 1: mixed}>
     */
    public function validAttributes(): array
    {
        return [
            'false'      => ['foo', false],
            'true'       => ['foo', true],
            'zero'       => ['foo', 0],
            'int'        => ['foo', 1],
            'zero-float' => ['foo', 0.0],
            'float'      => ['foo', 1.1],
            'string'     => ['foo', 'bar'],
            'string[]'   => ['foo', ['bar', 'baz']],
            'zero-key'   => ['0', 0],
        ];
    }

    /**
     * @dataProvider validAttributes
     */
    public function testWithAttributeReturnsNewInstanceForValidAttribute(string $name, mixed $value): void
    {
        $link = new Link('self');
        $new  = $link->withAttribute($name, $value);
        $this->assertNotSame($link, $new);
        $this->assertEquals([], $link->getAttributes());
        $this->assertEquals([$name => $value], $new->getAttributes());
    }

    public function testWithoutAttributeReturnsSameInstanceWhenAttributeIsNotPresent(): void
    {
        $link = new Link('self', '', false, ['foo' => 'bar']);
        $new  = $link->withoutAttribute('bar');
        $this->assertSame($link, $new);
    }

    public function testWithoutAttributeReturnsNewInstanceWhenAttributeCanBeRemoved(): void
    {
        $link = new Link('self', '', false, ['foo' => 'bar']);
        $new  = $link->withoutAttribute('foo');
        $this->assertNotSame($link, $new);
        $this->assertEquals(['foo' => 'bar'], $link->getAttributes());
        $this->assertEquals([], $new->getAttributes());
    }
}
