<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use ArgumentCountError;
use InvalidArgumentException;
use Mezzio\Hal\Link;
use PHPUnit\Framework\TestCase;
use Psr\Link\EvolvableLinkInterface;

class LinkTest extends TestCase
{
    public function testRequiresRelation(): void
    {
        $this->expectException(ArgumentCountError::class);
        new Link();
    }

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
        $attributes = ['foo' => 'bar', 'baz' => null];
        $link       = new Link('self', '', false, $attributes);
        $this->assertEquals(['self'], $link->getRels());
        $this->assertEquals($attributes, $link->getAttributes());
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

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidRelations(): array
    {
        return [
            'null'         => [null],
            'false'        => [false],
            'true'         => [true],
            'zero'         => [0],
            'int'          => [1],
            'zero-float'   => [0.0],
            'float'        => [1.1],
            'empty-string' => [''],
            'array'        => [['link']],
            'object'       => [(object) ['href' => 'link']],
        ];
    }

    /**
     * @dataProvider invalidRelations
     * @param mixed $rel
     */
    public function testWithRelRaisesExceptionForInvalidRelation($rel): void
    {
        $link = new Link('self');
        $this->expectException(InvalidArgumentException::class);
        $link->withRel($rel);
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

    /**
     * @dataProvider invalidRelations
     * @param mixed $rel
     */
    public function testWithoutRelReturnsSameInstanceIfRelationIsInvalid($rel): void
    {
        $link = new Link('self');
        $new  = $link->withoutRel($rel);
        $this->assertSame($link, $new);
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
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidUriTypes(): array
    {
        return [
            'null'         => [null],
            'false'        => [false],
            'true'         => [true],
            'zero'         => [0],
            'int'          => [1],
            'zero-float'   => [0.0],
            'float'        => [1.1],
            'array'        => [['link']],
            'plain-object' => [(object) ['href' => 'link']],
        ];
    }

    /**
     * @dataProvider invalidUriTypes
     * @param mixed $uri
     */
    public function testWithHrefRaisesExceptionForInvalidUriType($uri): void
    {
        $link = new Link('self');
        $this->expectException(InvalidArgumentException::class);
        $link->withHref($uri);
    }

    /**
     * @psalm-return iterable<string, array{0: string|object}>
     */
    public function validUriTypes(): iterable
    {
        yield 'string' => ['https://example.com/api/link'];
        yield 'castable-object' => [new TestAsset\Uri('https://example.com/api/link')];
    }

    /**
     * @dataProvider validUriTypes
     * @param string|object $uri
     */
    public function testWithHrefReturnsNewInstanceWhenUriIsValid($uri): void
    {
        $link = new Link('self', 'https://example.com');
        $new  = $link->withHref($uri);
        $this->assertNotSame($link, $new);

        /**
         * @psalm-suppress PossiblyInvalidCast
         */
        $stringHref = (string) $uri;
        $this->assertNotEquals($stringHref, $link->getHref());
        $this->assertEquals($stringHref, $new->getHref());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidAttributeNames(): array
    {
        return [
            'null'         => [null],
            'false'        => [false],
            'true'         => [true],
            'zero'         => [0],
            'int'          => [1],
            'zero-float'   => [0.0],
            'float'        => [1.1],
            'empty-string' => [''],
            'array'        => [['attribute']],
            'object'       => [(object) ['name' => 'attribute']],
        ];
    }

    /**
     * @dataProvider invalidAttributeNames
     * @param mixed $name
     */
    public function testWithAttributeRaisesExceptionForInvalidAttributeName($name): void
    {
        $link = new Link('self');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$name');
        $link->withAttribute($name, 'foo');
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
     * @param mixed $value
     */
    public function testWithAttributeRaisesExceptionForInvalidAttributeValue($value): void
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
        ];
    }

    /**
     * @dataProvider validAttributes
     * @param mixed $value
     */
    public function testWithAttributeReturnsNewInstanceForValidAttribute(string $name, $value): void
    {
        $link = new Link('self');
        $new  = $link->withAttribute($name, $value);
        $this->assertNotSame($link, $new);
        $this->assertEquals([], $link->getAttributes());
        $this->assertEquals([$name => $value], $new->getAttributes());
    }

    /**
     * @dataProvider invalidAttributeNames
     * @param mixed $name
     */
    public function testWithoutAttributeReturnsSameInstanceWhenAttributeNameIsInvalid($name): void
    {
        $link = new Link('self');
        $new  = $link->withoutAttribute($name);
        $this->assertSame($link, $new);
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
