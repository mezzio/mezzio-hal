<?php

declare(strict_types=1);

namespace MezzioTest\Hal\ResourceGenerator;

use Laminas\Hydrator\ExtractionInterface;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\Metadata\AbstractMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Mezzio\Hal\ResourceGenerator\Exception\UnexpectedMetadataTypeException;
use Mezzio\Hal\ResourceGenerator\RouteBasedResourceStrategy;
use Mezzio\Hal\ResourceGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use Stringable;

use function get_object_vars;
use function sprintf;

final class RouteBasedResourceStrategyTest extends TestCase
{
    private RouteBasedResourceStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new RouteBasedResourceStrategy();
    }

    public function testThrowsExceptionForInvalidMetadataType(): void
    {
        $metadata          = $this->createMock(AbstractMetadata::class);
        $resourceGenerator = $this->createMock(ResourceGeneratorInterface::class);
        $request           = $this->createMock(ServerRequestInterface::class);
        $instance          = new stdClass();

        $this->expectException(UnexpectedMetadataTypeException::class);

        $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );
    }

    public function testCreatesResourceWithScalarValues(): void
    {
        $instance = new class {
            public int $id      = 123;
            public string $name = 'Test Entity';
            public float $price = 99.99;
            public bool $active = true;
        };

        $metadata          = $this->createRouteBasedMetadata('api.entity');
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $this->assertInstanceOf(HalResource::class, $result);
        $data = $result->toArray();
        $this->assertEquals(123, $data['id']);
        $this->assertEquals('Test Entity', $data['name']);
        $this->assertEquals(99.99, $data['price']);
        $this->assertTrue($data['active']);
    }

    public function testConvertsStringableObjectsToStrings(): void
    {
        $stringableId = new class implements Stringable {
            public function __toString(): string
            {
                return 'uuid-12345';
            }
        };

        $stringableName = new class implements Stringable {
            public function __toString(): string
            {
                return 'Stringable Name';
            }
        };

        $instance = new class ($stringableId, $stringableName) {
            public function __construct(
                public readonly Stringable $id,
                public readonly Stringable $name
            ) {
            }
        };

        $metadata          = $this->createRouteBasedMetadata('api.entity');
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $data = $result->toArray();
        $this->assertEquals('uuid-12345', $data['id']);
        $this->assertEquals('Stringable Name', $data['name']);
    }

    public function testHandlesValueObjectsAsRouteParameters(): void
    {
        $userId = new class implements Stringable {
            public function __toString(): string
            {
                return 'user-456';
            }
        };

        $categoryId = new class implements Stringable {
            public function __toString(): string
            {
                return 'category-789';
            }
        };

        $instance = new class ($userId, $categoryId) {
            public function __construct(
                public readonly Stringable $userId,
                public readonly Stringable $categoryId,
                public readonly string $title = 'Test Product'
            ) {
            }
        };

        $metadata          = $this->createRouteBasedMetadataWithPlaceholders(
            'api.product',
            ['userId' => 'user_id', 'categoryId' => 'cat_id']
        );
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $this->assertInstanceOf(HalResource::class, $result);
        $data = $result->toArray();
        $this->assertEquals('user-456', $data['userId']);
        $this->assertEquals('category-789', $data['categoryId']);
        $this->assertEquals('Test Product', $data['title']);
    }

    /**
     * @dataProvider stringableValueObjectProvider
     */
    public function testHandlesVariousStringableValueObjects(Stringable $valueObject, string $expectedString): void
    {
        $instance = new class ($valueObject) {
            public function __construct(public readonly Stringable $id)
            {
            }
        };

        $metadata          = $this->createRouteBasedMetadata('api.test');
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $data = $result->toArray();
        $this->assertEquals($expectedString, $data['id']);
    }

    /**
     * @return array<string, array{0: Stringable, 1: string}>
     */
    public static function stringableValueObjectProvider(): array
    {
        return [
            'UUID value object'           => [
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return '550e8400-e29b-41d4-a716-446655440000';
                    }
                },
                '550e8400-e29b-41d4-a716-446655440000',
            ],
            'Email value object'          => [
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return 'user@example.com';
                    }
                },
                'user@example.com',
            ],
            'Custom ID value object'      => [
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return 'CUST-2024-001';
                    }
                },
                'CUST-2024-001',
            ],
            'Numeric string value object' => [
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return '123456789';
                    }
                },
                '123456789',
            ],
            'Empty string value object'   => [
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return '';
                    }
                },
                '',
            ],
        ];
    }

    public function testIgnoresNonScalarAndNonStringableValues(): void
    {
        $instance = new class {
            public int $id      = 1;
            public string $name = 'Test';
            public array $tags  = ['tag1', 'tag2'];
            public object $metadata;

            public function __construct()
            {
                $this->metadata = new stdClass();
            }
        };

        $metadata          = $this->createRouteBasedMetadata('api.entity');
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $data = $result->toArray();
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Test', $data['name']);
        $this->assertEquals(['tag1', 'tag2'], $data['tags']);
        $this->assertInstanceOf(stdClass::class, $data['metadata']);
    }

    public function testRespectsPlaceholderMapping(): void
    {
        $userId = new class implements Stringable {
            public function __toString(): string
            {
                return 'user-123';
            }
        };

        $instance = new class ($userId) {
            public function __construct(
                public readonly Stringable $userId,
                public readonly string $title = 'Test Item'
            ) {
            }
        };

        $metadata          = $this->createRouteBasedMetadataWithPlaceholders(
            'api.item',
            ['userId' => 'user_identifier']
        );
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $this->assertInstanceOf(HalResource::class, $result);
        $data = $result->toArray();
        $this->assertEquals('user-123', $data['userId']);
        $this->assertEquals('Test Item', $data['title']);
    }

    public function testHandlesComplexStringableValueObjects(): void
    {
        $complexId = new class implements Stringable {
            private string $prefix = 'COMPLEX';
            private int $number    = 12345;
            private string $suffix = 'XYZ';

            public function __toString(): string
            {
                return sprintf('%s-%d-%s', $this->prefix, $this->number, $this->suffix);
            }
        };

        $instance = new class ($complexId) {
            public function __construct(
                public readonly Stringable $complexId,
                public readonly int $version = 1
            ) {
            }
        };

        $metadata          = $this->createRouteBasedMetadata('api.complex');
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $data = $result->toArray();
        $this->assertEquals('COMPLEX-12345-XYZ', $data['complexId']);
        $this->assertEquals(1, $data['version']);
    }

    public function testHandlesMixedScalarAndStringableValues(): void
    {
        $uuid = new class implements Stringable {
            public function __toString(): string
            {
                return 'uuid-mixed-test';
            }
        };

        $instance = new class ($uuid) {
            public function __construct(
                public readonly Stringable $uuid,
                public readonly int $count = 42,
                public readonly string $status = 'active',
                public readonly float $score = 85.5,
                public readonly bool $enabled = true,
                public readonly array $nonScalar = ['ignored']
            ) {
            }
        };

        $metadata          = $this->createRouteBasedMetadata('api.mixed');
        $resourceGenerator = $this->createResourceGenerator();
        $request           = $this->createMock(ServerRequestInterface::class);

        $result = $this->strategy->createResource(
            $instance,
            $metadata,
            $resourceGenerator,
            $request
        );

        $data = $result->toArray();
        $this->assertEquals('uuid-mixed-test', $data['uuid']);
        $this->assertEquals(42, $data['count']);
        $this->assertEquals('active', $data['status']);
        $this->assertEquals(85.5, $data['score']);
        $this->assertTrue($data['enabled']);
        $this->assertEquals(['ignored'], $data['nonScalar']);
    }

    private function createRouteBasedMetadata(string $route): RouteBasedResourceMetadata
    {
        $metadata = $this->createMock(RouteBasedResourceMetadata::class);
        $metadata->method('getRoute')->willReturn($route);
        $metadata->method('getRouteParams')->willReturn([]);
        $metadata->method('getIdentifiersToPlaceholdersMapping')->willReturn([]);
        $metadata->method('hasReachedMaxDepth')->willReturn(false);

        return $metadata;
    }

    private function createRouteBasedMetadataWithPlaceholders(
        string $route,
        array $placeholders
    ): RouteBasedResourceMetadata {
        $metadata = $this->createMock(RouteBasedResourceMetadata::class);
        $metadata->method('getRoute')->willReturn($route);
        $metadata->method('getRouteParams')->willReturn([]);
        $metadata->method('getIdentifiersToPlaceholdersMapping')->willReturn($placeholders);
        $metadata->method('hasReachedMaxDepth')->willReturn(false);

        return $metadata;
    }

    private function createResourceGenerator(): ResourceGeneratorInterface
    {
        $linkGenerator = $this->createMock(LinkGenerator::class);
        $linkGenerator->method('fromRoute')->willReturn(
            new Link('self', '/test-url')
        );

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturn($this->createExtractor());

        $resourceGenerator = $this->createMock(ResourceGeneratorInterface::class);
        $resourceGenerator->method('getLinkGenerator')->willReturn($linkGenerator);
        $resourceGenerator->method('getHydrators')->willReturn($container);

        return $resourceGenerator;
    }

    private function createExtractor(): ExtractionInterface
    {
        return new class implements ExtractionInterface {
            public function extract(object $object): array
            {
                return get_object_vars($object);
            }
        };
    }
}
