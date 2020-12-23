# Migration From Version 1

While we strive to reduce backwards compatibility breaks, occasionally we find them necessary in order to either correct problems, make usage more predictable, or simplify maintenance.
Such changes are reserved for new major releases.

## New classes and interfaces

We extracted a new interface, `Mezzio\Hal\ResourceGeneratorInterface`, from the public API of `Mezzio\Hal\ResourceGenerator`.
Doing so allows providing alternate implementations in your own applications without requiring class extension.
The new interface has the following definition:

```php
namespace Mezzio\Hal;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResourceGeneratorInterface
{
    public function getHydrators(): ContainerInterface;

    public function getMetadataMap(): Metadata\MetadataMap;

    public function getLinkGenerator(): LinkGenerator;

    public function fromArray(array $data, ?string $uri = null): HalResource;

    public function fromObject(object $instance, ServerRequestInterface $request, int $depth = 0): HalResource;
}
```

Please note that the `fromObject()` method changes slightly:

- `$instance` now has the `object` typeint
- it adds the optional argument `int $depth = 0`; this argument is used to help identify and protect against circular references in nested resources.

`Mezzio\Hal\ResourceGenerator` now implements this interface.

## Signature changes

### Mezzio\Hal\ResourceGenerator

As noted in the previous section, the signature of the `fromObject()` method changes from:

```php
public function fromObject(
    $instance,
    Psr\Http\Message\ServerRequestInterface $request
): Mezzio\Hal\HalResource;
```

to:

```php
public function fromObject(
    object $instance,
    Psr\Http\Message\ServerRequestInterface $request
    int $depth = 0
): Mezzio\Hal\HalResource;
```

These changes should only impact users extending the `ResourceGenerator` class.

### Mezzio\Hal\LinkCollection

The trait `Mezzio\Hal\LinkCollection` provides implementation of the [PSR-13](https://www.php-fig.org/psr/psr-13/) `EvolvableLinkProviderInterface` that other classes may compose.
PHP allows adding return typehints to methods defined by an interface so long as the typehint is narrower than the original.
In the specification, these methods did not have an explicit return typehint, but did provide one via annotations.
As such, the following method signatures were updated to have the associated return typehints:

- `getLinks(): array`
- `getLinksByRel($rel): array`
- `withLink(Psr\Link\LinkInterface $link): self`
- `withoutLink(Psr\Link\LinkInterface $link): self`

These changes should not have any effect on users, as signatures of overridden trait methods do not need to match.

### Mezzio\Hal\ResourceGenerator\StrategyInterface

The signature of the `fromObject()` method originally was:

```php
public function createResource(
    $instance,
    Mezzio\Hal\Metadata\AbstractMetadata $metadata,
    Mezzio\Hal\ResourceGenerator $resourceGenerator,
    Psr\Http\Message\ServerRequestInterface $request
): Mezzio\Hal\HalResource;
```

and is now:

```php
public function createResource(
    object $instance,
    Mezzio\Hal\Metadata\AbstractMetadata $metadata,
    Mezzio\Hal\ResourceGeneratorInterface $resourceGenerator,
    Psr\Http\Message\ServerRequestInterface $request,
    int $depth = 0
): Mezzio\Hal\HalResource;
```

The changes:

- Enforce that the `$instance` value MUST be an object.
- Allow for alternate resource generator implementations so long as they implement the new `ResourceGeneratorInterface`.
- Provide the ability to protect against circular references in nested resources via the `$depth` value, which will generally be provided when the `$resourceGenerator` calls on a strategy implementation.

These changes also affect all shipped implementations of the `StrategyInterface`, including the following:

- `Mezzio\Hal\ResourceGenerator\RouteBasedCollectionStrategy`
- `Mezzio\Hal\ResourceGenerator\RouteBasedResourceStrategy`
- `Mezzio\Hal\ResourceGenerator\UrlBasedCollectionStrategy`
- `Mezzio\Hal\ResourceGenerator\UrlBasedResourceStrategy`

These changes will impact users who are extending one of the above classes, or providing their own `StrategyInterface` implementation.

### Mezzio\Hal\Metadata\AbstractMetadata

The `Mezzio\Hal\Metadata\AbstractMetadata` class, from which all other metadata classes derive, adds a new method, `hasReachedMaxDepth(int $currentDepth): bool`.
Extensions can override this method to provide logic for determining if traversal has reached the maximum depth, and is called by the resource generator in order to help prevent circular references in nested resources.

This change will only affect users creating their own metadata implementations; developers should look at how each of the shipped implementations define the method to determine how they should do so in their own classes.

### Mezzio\Hal\Metadata\RouteBasedResourceMetadata

The signature of the `__construct` method changes from:

```php
public function __construct(
    string $class,
    string $route,
    string $extractor,
    string $resourceIdentifier = 'id',
    string $routeIdentifierPlaceholder = 'id',
    array $routeParams = [],
    array $identifiersToPlaceholdersMapping = [],
    int $maxDepth = 10
) {
```

to:

```php
public function __construct(
    string $class,
    string $route,
    string $extractor,
    string $resourceIdentifier = 'id',
    array $routeParams = [],
    array $identifiersToPlaceholdersMapping = [],
    int $maxDepth = 10
) {
```

Note the removal of the `$routeIdentifierPlaceholder` argument.

If you were previously using this argument, you will need to instead populate the `$identifiersToPlaceholdersMapping` argument.
As an example, if you had an entity that defined an "id" property as an identifier, but your route definition used "calc_id" as the equivalent (e.g., `/calculation/{calc_id:\d+}`), you might have called the constructor as follows:

```php
$metadata = new RouteBasedResourceMetadata(
    CalculationEntity::class,
    'calculation',
    ObjectHydrator::class,
    'id',
    'calc_id'
);
```

This would change to:

```php
$metadata = new RouteBasedResourceMetadata(
    CalculationEntity::class,
    'calculation',
    ObjectHydrator::class,
    'id',
    [],
    ['id' => 'calc_id']
);
```

The new logic provides an explicit mapping of any resource property to their corresponding route placeholders, no longer limiting you to only mapping resource identifiers.

### Mezzio\Hal\HalResource

The method `jsonSerialize()` now has a return typehint of `array`; this was implied previously by the `JsonSerializable` interface the method provides an implementation for, but is now explicit.

This change will only affect those extending the `HalResource` class.

### Mezzio\Hal\Exception\UndefinedMetadataException

The signature of the `create()` method changes from:

```php
public static function create($class)
```

to:

```php
public static function create(string $class): self
```

This change will only affect extensions to this class.

### Mezzio\Hal\Exception\UndefinedClassException

The signature of the `create()` method changes from:

```php
public static function create($class)
```

to:

```php
public static function create(string $class): self
```

This change will only affect extensions to this class.

### Mezzio\Hal\Exception\DuplicateMetadataException

The signature of the `create()` method changes from:

```php
public static function create(string $class)
```

to:

```php
public static function create(string $class): self
```

This change will only affect extensions to this class.

## Configuration changes

### route_identifier_placeholder removed

In version 1, the configuration setting `route_identifier_placeholder` could be used when configuring `RouteBasedResourceMetadata`, and was used to indicate the route placeholder that corresponded with the resource identifier.

Version 2 removes this setting.
Users should instead use the `identifiers_to_placeholders_mapping` setting introduced in the 1.4 series.
This configuration allows you to map not just the resource identifier to its route placeholder; with it, you can map ANY resource property that has associated route placeholders.
This is particularly interesting when creating nested resources, where the parent resource identifier may be present in the resource itself, allowing routing to the nested resource easily.

As an initial example, we'll cover the original use case for a `route_identifier_placeholder` on its own.
Let's consider an entity that defined an "id" property as an identifier, but your route definition used "calc_id" as the equivalent (e.g., `/calculation/{calc_id:\d+}`).
You might have configured it as follows:

```php
use Laminas\Hydrator\ObjectHydrator;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;

return [
    MetadataMap::class => [
        [
            '__class__'                    => RouteBasedResourceMetadata::class,
            'resource_class'               => CalculationEntity::class,
            'route'                        => 'calculation',
            'extractor'                    => ObjectHydrator::class,
            'resource_identifier'          => 'id',
            'route_identifier_placeholder' => 'calc_id',
        ]
    ],
];
```

With v2, this should be rewritten as:

```php
use Laminas\Hydrator\ObjectHydrator;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;

return [
    MetadataMap::class => [
        [
            '__class__'                           => RouteBasedResourceMetadata::class,
            'resource_class'                      => CalculationEntity::class,
            'route'                               => 'calculation',
            'extractor'                           => ObjectHydrator::class,
            'resource_identifier'                 => 'id',
            'identifiers_to_placeholders_mapping' => [
                'id' => 'calc_id',
            ],
        ]
    ],
];
```

To expand on this example, and demonstrate the new capabilities possible, let's consider a route definition that also expected a value for an "id" associated with the resource's "transaction_id" property (e.g., `/transaction/{id:\d+}/calculation/{calc_id:\d+}`).
You could configure that as follows:

```php
use Laminas\Hydrator\ObjectHydrator;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;

return [
    MetadataMap::class => [
        [
            '__class__'                           => RouteBasedResourceMetadata::class,
            'resource_class'                      => CalculationEntity::class,
            'route'                               => 'calculation',
            'extractor'                           => ObjectHydrator::class,
            'resource_identifier'                 => 'id',
            'identifiers_to_placeholders_mapping' => [
                'id'             => 'calc_id',
                'transaction_id' => 'id',
            ],
        ]
    ],
];
```

In this scenario, the `id` property of the resource will be substituted for the `calc_id` route placeholder, and the `transaction_id` property will be substituted for the `id` route placeholder.
