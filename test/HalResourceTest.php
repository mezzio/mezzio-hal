<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Generator;
use InvalidArgumentException;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function array_values;
use function file_get_contents;
use function is_array;
use function json_decode;

class HalResourceTest extends TestCase
{
    public function testCanConstructWithData(): void
    {
        $resource = new HalResource(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $resource->getElements());
    }

    /**
     * @psalm-return array<string, array{0: string, 1: string}>
     */
    public function invalidElementNames(): array
    {
        return [
            'empty'     => ['', 'cannot be empty'],
            '_links'    => ['_links', 'reserved element $name'],
            '_embedded' => ['_embedded', 'reserved element $name'],
        ];
    }

    /**
     * @dataProvider invalidElementNames
     */
    public function testInvalidDataNamesRaiseExceptionsDuringConstruction(string $name, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new HalResource([$name => 'bar']);
    }

    public function testCanConstructWithDataContainingEmbeddedResources(): void
    {
        $embedded = new HalResource(['foo' => 'bar']);
        $resource = new HalResource(['foo' => $embedded]);
        $this->assertEquals(['foo' => $embedded], $resource->getElements());
        $representation = $resource->toArray();
        $this->assertArrayHasKey('_embedded', $representation);
        $this->assertArrayHasKey('foo', $representation['_embedded']);
        $this->assertEquals(['foo' => 'bar'], $representation['_embedded']['foo']);
    }

    public function testCanConstructWithLinks(): void
    {
        $links    = [
            new Link('self', 'https://example.com/'),
            new Link('about', 'https://example.com/about'),
        ];
        $resource = new HalResource([], $links);
        $this->assertSame($links, $resource->getLinks());
    }

    public function testNonLinkItemsRaiseExceptionDuringConstruction(): void
    {
        $links = [
            new Link('self', 'https://example.com/'),
            'foo',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$links');
        new HalResource([], $links);
    }

    public function testCanConstructWithEmbeddedResources(): void
    {
        $embedded = new HalResource(['foo' => 'bar']);
        $resource = new HalResource([], [], ['foo' => $embedded]);
        $this->assertEquals(['foo' => $embedded], $resource->getElements());
        $representation = $resource->toArray();
        $this->assertArrayHasKey('_embedded', $representation);
        $this->assertArrayHasKey('foo', $representation['_embedded']);
        $this->assertEquals(['foo' => 'bar'], $representation['_embedded']['foo']);
    }

    public function testNonResourceOrCollectionItemsRaiseExceptionDuringConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid embedded resource');
        new HalResource([], [], ['foo' => 'bar']);
    }

    public function testEmptyArrayAsDataWillNotBeEmbeddedDuringConstruction(): void
    {
        $resource = new HalResource(['bar' => []]);
        $this->assertEquals(['bar' => []], $resource->getElements());
        $representation = $resource->toArray();
        $this->assertArrayNotHasKey('_embeded', $representation);
    }

    /**
     * @dataProvider invalidElementNames
     */
    public function testInvalidResourceNamesRaiseExceptionsDuringConstruction(
        string $name,
        string $expectedMessage
    ): void {
        $embedded = new HalResource(['foo' => 'bar']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        /**
         * @psalm-suppress InvalidArgument
         */
        new HalResource([], [], [$name => $embedded]);
    }

    public function testWithLinkReturnsNewInstanceContainingNewLink(): void
    {
        $link     = new Link('self');
        $resource = new HalResource();
        $new      = $resource->withLink($link);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getLinksByRel('self'));
        $this->assertEquals([$link], $new->getLinksByRel('self'));
    }

    public function testWithLinkReturnsSameInstanceIfAlreadyContainsLinkInstance(): void
    {
        $link     = new Link('self');
        $resource = new HalResource([], [$link]);
        $new      = $resource->withLink($link);
        $this->assertSame($resource, $new);
    }

    public function testWithoutLinkReturnsNewInstanceRemovingLink(): void
    {
        $link     = new Link('self');
        $resource = new HalResource([], [$link]);
        $new      = $resource->withoutLink($link);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([$link], $resource->getLinksByRel('self'));
        $this->assertEquals([], $new->getLinksByRel('self'));
    }

    public function testWithoutLinkReturnsSameInstanceIfLinkIsNotPresent(): void
    {
        $link     = new Link('self');
        $resource = new HalResource();
        $new      = $resource->withoutLink($link);
        $this->assertSame($resource, $new);
    }

    public function testGetLinksByRelReturnsAllLinksWithGivenRelationshipAsArray(): void
    {
        $link1    = new Link('self');
        $link2    = new Link('about');
        $link3    = new Link('self');
        $resource = new HalResource();

        $resource = $resource
            ->withLink($link1)
            ->withLink($link2)
            ->withLink($link3);

        $links = $resource->getLinksByRel('self');
        // array_values needed here, as keys will no longer be sequential
        $this->assertEquals([$link1, $link3], array_values($links));
    }

    /**
     * @dataProvider invalidElementNames
     */
    public function testWithElementRaisesExceptionForInvalidName(string $name, string $expectedMessage): void
    {
        $resource = new HalResource();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        $resource->withElement($name, 'foo');
    }

    public function testWithElementRaisesExceptionIfNameCollidesWithExistingResource(): void
    {
        $embedded = new HalResource(['foo' => 'bar']);
        $resource = new HalResource(['foo' => $embedded]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('element matching resource');
        $resource->withElement('foo', 'bar');
    }

    public function testWithElementReturnsNewInstanceWithNewElement(): void
    {
        $resource = new HalResource();
        $new      = $resource->withElement('foo', 'bar');
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => 'bar'], $new->getElements());
    }

    public function testWithElementReturnsNewInstanceOverwritingExistingElementValue(): void
    {
        $resource = new HalResource(['foo' => 'bar']);
        $new      = $resource->withElement('foo', 'baz');
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => 'bar'], $resource->getElements());
        $this->assertEquals(['foo' => 'baz'], $new->getElements());
    }

    public function testWithElementProxiesToEmbedIfResourceValueProvided(): void
    {
        $embedded = new HalResource(['foo' => 'bar']);
        $resource = new HalResource();
        $new      = $resource->withElement('foo', $embedded);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => $embedded], $new->getElements());
        $representation = $new->toArray();
        $this->assertArrayHasKey('_embedded', $representation);
        $this->assertArrayHasKey('foo', $representation['_embedded']);
        $this->assertEquals(['foo' => 'bar'], $representation['_embedded']['foo']);
    }

    public function testWithElementProxiesToEmbedIfResourceCollectionValueProvided(): void
    {
        $resource1  = new HalResource(['foo' => 'bar']);
        $resource2  = new HalResource(['foo' => 'baz']);
        $resource3  = new HalResource(['foo' => 'bat']);
        $collection = [$resource1, $resource2, $resource3];

        $resource = new HalResource();
        $new      = $resource->withElement('foo', $collection);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => $collection], $new->getElements());
    }

    public function testWithElementDoesNotProxyToEmbedIfAnEmptyArrayValueIsProvided(): void
    {
        $resource = new HalResource(['foo' => 'bar'], embedEmptyCollections: false);
        $new      = $resource->withElement('bar', []);

        $representation = $new->toArray();
        self::assertSame(['foo' => 'bar', 'bar' => []], $representation);
    }

    public function testWithElementWillEmbedAnEmptyArrayIfAnEmptyArrayValueIsProvidedAndConfiguredToEmbedEmptyCollections(): void
    {
        $resource = new HalResource(['foo' => 'bar'], embedEmptyCollections: true);
        $new      = $resource->withElement('bar', []);

        $representation = $new->toArray();
        self::assertSame(['foo' => 'bar', '_embedded' => ['bar' => []]], $representation);
    }

    public function testWithElementDoesNotProxyToEmbedIfNullValueIsProvidedAndEmbedEmptyCollectionsEnabled(): void
    {
        $resource = new HalResource(['foo' => 'bar'], [], [], true);
        $new      = $resource->withElement('bar', null);

        $representation = $new->toArray();
        self::assertSame(['foo' => 'bar', 'bar' => null], $representation);
    }

    /**
     * @dataProvider invalidElementNames
     */
    public function testEmbedRaisesExceptionForInvalidName(string $name, string $expectedMessage): void
    {
        $resource = new HalResource();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        $resource->embed($name, new HalResource());
    }

    public function testEmbedRaisesExceptionIfNameCollidesWithExistingData(): void
    {
        $resource = new HalResource(['foo' => 'bar']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('embed resource matching element');
        $resource->embed('foo', new HalResource());
    }

    public function testEmbedReturnsNewInstanceWithEmbeddedResource(): void
    {
        $embedded = new HalResource(['foo' => 'bar']);
        $resource = new HalResource();
        $new      = $resource->embed('foo', $embedded);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => $embedded], $new->getElements());
    }

    public function testEmbedReturnsNewInstanceWithEmbeddedCollection(): void
    {
        $resource1  = new HalResource(['foo' => 'bar']);
        $resource2  = new HalResource(['foo' => 'baz']);
        $resource3  = new HalResource(['foo' => 'bat']);
        $collection = [$resource1, $resource2, $resource3];

        $resource = new HalResource();
        $new      = $resource->embed('foo', $collection);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => $collection], $new->getElements());
    }

    public function testEmbedReturnsNewInstanceAppendingResourceToExistingResource(): void
    {
        $resource1 = new HalResource(['foo' => 'bar']);
        $resource2 = new HalResource(['foo' => 'baz']);

        $resource = new HalResource(['foo' => $resource1]);
        $new      = $resource->embed('foo', $resource2);
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => $resource1], $resource->getElements());
        $this->assertEquals(['foo' => [$resource1, $resource2]], $new->getElements());
    }

    public function testEmbedReturnsNewInstanceAppendingResourceToExistingCollection(): void
    {
        $resource1  = new HalResource(['foo' => 'bar']);
        $resource2  = new HalResource(['foo' => 'baz']);
        $resource3  = new HalResource(['foo' => 'bat']);
        $collection = [$resource1, $resource2];

        $resource = new HalResource(['foo' => $collection]);
        $new      = $resource->embed('foo', $resource3);
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => $collection], $resource->getElements());
        $this->assertEquals(['foo' => [$resource1, $resource2, $resource3]], $new->getElements());
    }

    public function testEmbedReturnsNewInstanceAppendingCollectionToExistingCollection(): void
    {
        $resource1   = new HalResource(['foo' => 'bar']);
        $resource2   = new HalResource(['foo' => 'baz']);
        $resource3   = new HalResource(['foo' => 'bat']);
        $resource4   = new HalResource(['foo' => 'bat']);
        $collection1 = [$resource1, $resource2];
        $collection2 = [$resource3, $resource4];

        $resource = new HalResource(['foo' => $collection1]);
        $new      = $resource->embed('foo', $collection2);
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => $collection1], $resource->getElements());
        $this->assertEquals(['foo' => $collection1 + $collection2], $new->getElements());
    }

    public function testCanEmbedResourceEvenIfNewResourceDoesNotMatchStructureOfExistingOne(): void
    {
        self::assertSame(
            [
                '_embedded' => [
                    'foo' => [
                        ['foo' => 'bar'],
                        ['bar' => 'baz'],
                    ],
                ],
            ],
            (new HalResource(['foo' => new HalResource(['foo' => 'bar'])]))
                ->embed('foo', new HalResource(['bar' => 'baz']))
                ->toArray()
        );
    }

    public function testCanEmbedResourceEvenIfNewResourceDoesNotMatchCollectionResourceStructure(): void
    {
        self::assertSame(
            [
                '_embedded' => [
                    'foo' => [
                        ['foo' => 'bar'],
                        ['foo' => 'baz'],
                        ['bar' => 'bat'],
                    ],
                ],
            ],
            (new HalResource([
                'foo' => [
                    new HalResource(['foo' => 'bar']),
                    new HalResource(['foo' => 'baz']),
                ],
            ]))
                ->embed('foo', new HalResource(['bar' => 'bat']))
                ->toArray()
        );
    }

    /**
     * HAL resources of different types can be embedded within the same parent: this is normal, since
     * it is very much normal for JSONSchema to contain union types representing different types.
     *
     * In this example, we designed a traditional ECommerce scenario, where different products may
     * have different field structure.
     *
     * @see https://github.com/mezzio/mezzio-hal/issues/50
     */
    public function testAllowsEmbeddedResourcesWithDifferentObjectProperties(): void
    {
        $resource = new HalResource(
            ['id' => 123],
            [],
            [
                'some.resource' => [
                    new HalResource([
                        'id'     => 456,
                        'name'   => 'a very cool hat',
                        'colour' => 'yellow',
                    ]),
                    new HalResource([
                        'id'      => 678,
                        'name'    => 'industrial cleaner',
                        'flavour' => 'lemongrass', // Intentional: array key is different between cleaners and clothing!
                    ]),
                ],
            ]
        );

        self::assertSame(
            [
                'id'        => 123,
                '_embedded' => [
                    'some.resource' => [
                        [
                            'id'     => 456,
                            'name'   => 'a very cool hat',
                            'colour' => 'yellow',
                        ],
                        [
                            'id'      => 678,
                            'name'    => 'industrial cleaner',
                            'flavour' => 'lemongrass',
                        ],
                    ],
                ],
            ],
            $resource->toArray()
        );
    }

    public function testWithElementsAddsNewDataToNewResourceInstance(): void
    {
        $resource = new HalResource();
        $new      = $resource->withElements(['foo' => 'bar']);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => 'bar'], $new->getElements());
    }

    public function testWithElementsAddsNewEmbeddedResourcesToNewResourceInstance(): void
    {
        $embedded = new HalResource(['foo' => 'bar']);
        $resource = new HalResource();
        $new      = $resource->withElements(['foo' => $embedded]);
        $this->assertNotSame($resource, $new);
        $this->assertEquals([], $resource->getElements());
        $this->assertEquals(['foo' => $embedded], $new->getElements());
        $representation = $new->toArray();
        $this->assertArrayHasKey('_embedded', $representation);
        $this->assertArrayHasKey('foo', $representation['_embedded']);
        $this->assertEquals(['foo' => 'bar'], $representation['_embedded']['foo']);
    }

    public function testWithElementsOverwritesExistingDataInNewResourceInstance(): void
    {
        $resource = new HalResource(['foo' => 'bar']);
        $new      = $resource->withElements(['foo' => 'baz']);
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => 'bar'], $resource->getElements());
        $this->assertEquals(['foo' => 'baz'], $new->getElements());
    }

    public function testWithElementsAppendsEmbeddedResourcesToExistingResourcesInNewResourceInstance(): void
    {
        $resource1 = new HalResource(['foo' => 'bar']);
        $resource2 = new HalResource(['foo' => 'bar']);
        $resource  = new HalResource(['foo' => $resource1]);
        $new       = $resource->withElements(['foo' => $resource2]);

        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => $resource1], $resource->getElements());
        $this->assertEquals(['foo' => [$resource1, $resource2]], $new->getElements());
    }

    public function testWithoutElementRemovesDataElementIfItIsPresent(): void
    {
        $resource = new HalResource(['foo' => 'bar']);
        $new      = $resource->withoutElement('foo');
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => 'bar'], $resource->getElements());
        $this->assertEquals([], $new->getElements());
    }

    public function testWithoutElementDoesNothingIfElementOrResourceNotPresent(): void
    {
        $resource = new HalResource(['foo' => 'bar']);
        $new      = $resource->withoutElement('bar');
        $this->assertSame($resource, $new);
    }

    public function testWithoutElementRemovesEmbeddedResourceIfItIsPresent(): void
    {
        $embedded = new HalResource();
        $resource = new HalResource(['foo' => $embedded]);
        $new      = $resource->withoutElement('foo');
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => $embedded], $resource->getElements());
        $this->assertEquals([], $new->getElements());
    }

    public function testWithoutElementRemovesEmbeddedCollectionIfPresent(): void
    {
        $resource1  = new HalResource();
        $resource2  = new HalResource();
        $resource3  = new HalResource();
        $collection = [$resource1, $resource2, $resource3];
        $resource   = new HalResource(['foo' => $collection]);
        $new        = $resource->withoutElement('foo');
        $this->assertNotSame($resource, $new);
        $this->assertEquals(['foo' => $collection], $resource->getElements());
        $this->assertEquals([], $new->getElements());
    }

    /**
     * @dataProvider invalidElementNames
     */
    public function testWithoutElementRaisesExceptionForInvalidElementName(string $name, string $expectedMessage): void
    {
        $resource = new HalResource();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        $resource->withoutElement($name);
    }

    /**
     * @psalm-return iterable<string, array{0: HalResource, 1: array}>
     */
    public function populatedResources(): iterable
    {
        $resource = (new HalResource())
            ->withLink(new Link('self', '/api/foo'))
            ->withLink(new Link('about', '/doc/about'))
            ->withLink(new Link('about', '/doc/resources/foo'))
            ->withElements(['foo' => 'bar', 'id' => 12345678])
            ->embed('bar', new HalResource(['bar' => 'baz'], [new Link('self', '/api/bar')]))
            ->embed('baz', [
                new HalResource(['baz' => 'bat', 'id' => 987654], [new Link('self', '/api/baz/987654')]),
                new HalResource(['baz' => 'bat', 'id' => 987653], [new Link('self', '/api/baz/987653')]),
            ]);
        $expected = [
            'foo'       => 'bar',
            'id'        => 12345678,
            '_links'    => [
                'self'  => [
                    'href' => '/api/foo',
                ],
                'about' => [
                    ['href' => '/doc/about'],
                    ['href' => '/doc/resources/foo'],
                ],
            ],
            '_embedded' => [
                'bar' => [
                    'bar'    => 'baz',
                    '_links' => [
                        'self' => ['href' => '/api/bar'],
                    ],
                ],
                'baz' => [
                    [
                        'baz'    => 'bat',
                        'id'     => 987654,
                        '_links' => [
                            'self' => ['href' => '/api/baz/987654'],
                        ],
                    ],
                    [
                        'baz'    => 'bat',
                        'id'     => 987653,
                        '_links' => [
                            'self' => ['href' => '/api/baz/987653'],
                        ],
                    ],
                ],
            ],
        ];

        yield 'fully-populated' => [$resource, $expected];
    }

    /**
     * @dataProvider populatedResources
     */
    public function testToArrayReturnsHalDataStructure(HalResource $resource, array $expected): void
    {
        $this->assertEquals($expected, $resource->toArray());
    }

    /**
     * @dataProvider populatedResources
     */
    public function testJsonSerializeReturnsHalDataStructure(HalResource $resource, array $expected): void
    {
        $this->assertEquals($expected, $resource->jsonSerialize());
    }

    public function testAllowsForcingResourceToAggregateAsACollection(): void
    {
        $resource = (new HalResource())
            ->withLink(new Link('self', '/api/foo'))
            ->embed(
                'bar',
                new HalResource(['bar' => 'baz'], [new Link('self', '/api/bar')]),
                true
            );

        $expected = [
            '_links'    => [
                'self' => [
                    'href' => '/api/foo',
                ],
            ],
            '_embedded' => [
                'bar' => [
                    [
                        'bar'    => 'baz',
                        '_links' => [
                            'self' => ['href' => '/api/bar'],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $resource->toArray());
    }

    public function testAllowsForcingLinkToAggregateAsACollection(): void
    {
        $link     = new Link('foo', '/api/foo', false, [Link::AS_COLLECTION => true]);
        $resource = new HalResource(['id' => 'foo'], [$link]);

        $expected = [
            '_links' => [
                'foo' => [
                    [
                        'href' => '/api/foo',
                    ],
                ],
            ],
            'id'     => 'foo',
        ];

        $this->assertEquals($expected, $resource->toArray());
    }

    private function fixture(string $file): array
    {
        $contents = file_get_contents(__DIR__ . '/Fixture/' . $file);

        if ($contents === false) {
            throw new RuntimeException('Failed to read fixture file: ' . $file);
        }

        $json = json_decode($contents, true);
        if (! is_array($json)) {
            throw new RuntimeException('Failed to json_decode fixture file: ' . $file);
        }

        return $json;
    }

    /**
     * @return Generator<string,array<array-key,array<array-key,HalResource>>>
     */
    public static function nonEmptyCollectionDataProvider(): Generator
    {
        yield from [
            'collection' => [
                [
                    (new HalResource())->withElements([
                        'id'    => 1,
                        'name'  => 'John',
                        'email' => 'john@example.com',
                    ]),
                    (new HalResource())->withElements([
                        'id'    => 2,
                        'name'  => 'Jane',
                        'email' => 'jane@example.com',
                    ]),
                ],
            ],
        ];
    }

    /**
     * @return Generator<'array',list{array<never,never>},mixed,void>
     */
    public static function emptyCollectionDataProvider(): Generator
    {
        yield from [
            'array' => [[]],
        ];
    }

    /**
     * @dataProvider emptyCollectionDataProvider
     */
    public function testEmptyCollectionWhenEmbedEmptyEnabled(mixed $collection): void
    {
        $resource = (new HalResource([], [], [], true))
            ->withLink(new Link('self', '/api/contacts'))
            ->withElements(['contacts' => $collection]);

        self::assertSame(
            $this->fixture('empty-contacts-collection.json'),
            $resource->toArray()
        );
    }

    /**
     * @dataProvider nonEmptyCollectionDataProvider
     */
    public function testNonEmptyCollection(mixed $collection): void
    {
        $resource = (new HalResource())
            ->withLink(new Link('self', '/api/contacts'))
            ->withElements(['contacts' => $collection]);

        self::assertSame(
            $this->fixture('non-empty-contacts-collection.json'),
            $resource->toArray()
        );
    }

    /**
     * @return Generator<'null',list{null},mixed,void>
     */
    public static function nullCollectionDataProvider(): Generator
    {
        yield from [
            'null' => [null],
        ];
    }

    /**
     * @dataProvider nullCollectionDataProvider
     */
    public function testNullCollectionWhenEmbedEmtpyEnabled(mixed $collection): void
    {
        $resource = (new HalResource([], [], [], true))
            ->withLink(new Link('self', '/api/contacts'))
            ->withElements(['contacts' => $collection]);

        self::assertSame(
            $this->fixture('null-contacts-collection.json'),
            $resource->toArray()
        );
    }
}
