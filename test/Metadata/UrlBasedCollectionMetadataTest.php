<?php

declare(strict_types=1);

namespace MezzioTest\Hal\Metadata;

use InvalidArgumentException;
use Mezzio\Hal\Metadata\AbstractCollectionMetadata;
use Mezzio\Hal\Metadata\UrlBasedCollectionMetadata;
use PHPUnit\Framework\TestCase;

use function sprintf;

final class UrlBasedCollectionMetadataTest extends TestCase
{
    public function testConstructorWithValidParameters(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items',
            'page',
            AbstractCollectionMetadata::TYPE_QUERY
        );

        $this->assertSame('TestClass', $metadata->getClass());
        $this->assertSame('items', $metadata->getCollectionRelation());
        $this->assertSame('/api/items', $metadata->getUrl());
        $this->assertSame('page', $metadata->getPaginationParam());
        $this->assertSame(AbstractCollectionMetadata::TYPE_QUERY, $metadata->getPaginationParamType());
    }

    public function testConstructorWithDefaultParameters(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items'
        );

        $this->assertSame('TestClass', $metadata->getClass());
        $this->assertSame('items', $metadata->getCollectionRelation());
        $this->assertSame('/api/items', $metadata->getUrl());
        $this->assertSame('page', $metadata->getPaginationParam());
        $this->assertSame(AbstractCollectionMetadata::TYPE_QUERY, $metadata->getPaginationParamType());
    }

    public function testConstructorWithPlaceholderPaginationType(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items/{page}',
            'page',
            AbstractCollectionMetadata::TYPE_PLACEHOLDER
        );

        $this->assertSame(AbstractCollectionMetadata::TYPE_PLACEHOLDER, $metadata->getPaginationParamType());
    }

    public function testConstructorThrowsExceptionForEmptyCollectionRelation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$collectionRelation MUST NOT be empty');

        new UrlBasedCollectionMetadata(
            'TestClass',
            '',
            '/api/items'
        );
    }

    public function testConstructorZeroCollectionRelation(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            '0',
            '/api/items'
        );

        $this->assertSame('0', $metadata->getCollectionRelation());
    }

    public function testConstructorThrowsExceptionForEmptyPaginationParam(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$paginationParam MUST NOT be empty');

        new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items',
            ''
        );
    }

    public function testConstructorZeroPaginationParam(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items',
            '0'
        );

        $this->assertSame('0', $metadata->getPaginationParam());
    }

    public function testConstructorThrowsExceptionForInvalidPaginationParamType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$paginationParamType MUST be one of "placeholder" or "query"; received "invalid"'
        );

        new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items',
            'page',
            'invalid'
        );
    }

    public function testConstructorWithZeroClass(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            '0',
            'items',
            '/api/items'
        );

        $this->assertSame('0', $metadata->getClass());
    }

    public function testConstructorWithZeroUrl(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '0'
        );

        $this->assertSame('0', $metadata->getUrl());
    }

    public function testGetUrl(): void
    {
        $url      = '/api/v1/items';
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            $url
        );

        $this->assertSame($url, $metadata->getUrl());
    }

    public function testConstructorWithNumericStringValues(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            '123',
            '456',
            '/api/789',
            '0page'
        );

        $this->assertSame('123', $metadata->getClass());
        $this->assertSame('456', $metadata->getCollectionRelation());
        $this->assertSame('/api/789', $metadata->getUrl());
        $this->assertSame('0page', $metadata->getPaginationParam());
    }

    public function testConstructorWithWhitespaceValues(): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            ' ',
            ' items ',
            ' /api/items ',
            ' page '
        );

        $this->assertSame(' ', $metadata->getClass());
        $this->assertSame(' items ', $metadata->getCollectionRelation());
        $this->assertSame(' /api/items ', $metadata->getUrl());
        $this->assertSame(' page ', $metadata->getPaginationParam());
    }

    /**
     * @dataProvider validPaginationParamTypeProvider
     */
    public function testConstructorWithValidPaginationParamTypes(string $type): void
    {
        $metadata = new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items',
            'page',
            $type
        );

        $this->assertSame($type, $metadata->getPaginationParamType());
    }

    /**
     * @return array<string, string[]>
     */
    public static function validPaginationParamTypeProvider(): array
    {
        return [
            'placeholder type' => [AbstractCollectionMetadata::TYPE_PLACEHOLDER],
            'query type'       => [AbstractCollectionMetadata::TYPE_QUERY],
        ];
    }

    /**
     * @dataProvider invalidPaginationParamTypeProvider
     */
    public function testConstructorThrowsExceptionForInvalidPaginationParamTypes(string $invalidType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                '$paginationParamType MUST be one of "%s" or "%s"; received "%s"',
                AbstractCollectionMetadata::TYPE_PLACEHOLDER,
                AbstractCollectionMetadata::TYPE_QUERY,
                $invalidType
            )
        );

        new UrlBasedCollectionMetadata(
            'TestClass',
            'items',
            '/api/items',
            'page',
            $invalidType
        );
    }

    /**
     * @return array<string, string[]>
     */
    public static function invalidPaginationParamTypeProvider(): array
    {
        return [
            'empty string'        => [''],
            'zero string'         => ['0'],
            'random string'       => ['invalid'],
            'space'               => [' '],
            'null-like string'    => ['null'],
            'boolean-like string' => ['true'],
        ];
    }
}
