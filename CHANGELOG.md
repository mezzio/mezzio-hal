# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

Versions prior to 0.4.0 were released as the package "weierophinney/hal".

## 2.0.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.0 - 2020-12-23

### Added

- [#19](https://github.com/mezzio/mezzio-hal/pull/19) extracts the interface `ResourceGeneratorInterface` to describe the behaviors of a resource generator.

### Changed

- [#29](https://github.com/mezzio/mezzio-hal/pull/29) makes the following signature changes to the `LinkCollection` class:
  - `getLinks()` gets an `array` return typehint
  - `getLinksByRel()` gets an `array` return typehint
  - `withLink()` gets a `self` return typehint
  - `withoutLink()` gets a `self` return typehint

- [#28](https://github.com/mezzio/mezzio-hal/pull/28) adds the `object` typehint to the `$instance` argument of the `StrategyInterface::fromObject()` method.
  This also affects each of the shipped implementations:
  - `RouteBasedCollectionStrategy`
  - `RouteBasedResourceStrategy`
  - `UrlBasedCollectionStrategy`
  - `UrlBasedResourceStrategy`

- [#28](https://github.com/mezzio/mezzio-hal/pull/28) adds the `string` typehint to the `$class` argument and a `self` return typehint to `UndefinedMetadataException::create()`.

- [#28](https://github.com/mezzio/mezzio-hal/pull/28) adds the `string` typehint to the `$class` argument and a `self` return typehint to `UndefinedClassException::create()`.

- [#28](https://github.com/mezzio/mezzio-hal/pull/28) adds a `self` return typehint to `DuplicateMetadataException::create()`.

- [#28](https://github.com/mezzio/mezzio-hal/pull/28) adds an `array` return typehint to `HalResource::jsonSerialize()`. It was implied before, but is now made explicit.

- [#22](https://github.com/mezzio/mezzio-hal/pull/22) changes the signature of `ResourceGenerator::fromObject()` to accept an additional, optional `int $depth = 0` argument. This is used to help prevent circular references.

- [#22](https://github.com/mezzio/mezzio-hal/pull/22) adds the method `hasReachedMaxDepth()` to each of the `AbstractMetadata` and `AbstractResourceMetadata` classes, and each metadata implementation now also accepts an additional optional `$maxDepth` argument (with related `max_depth` configuration setting). These are used in conjunction with strategy implementations to prevent circular references.

- [#22](https://github.com/mezzio/mezzio-hal/pull/22) adds an optional `$depth` argument as the last argument to `StrategyInterface::createResource()` and the same method on all strategy implementations; the argument is used to prevent circular referencing.

- [#19](https://github.com/mezzio/mezzio-hal/pull/19) updates all typehints that previously referenced `ResourceGenerator` to now reference `ResourceGeneratorInterface`. This includes `Mezzio\Hal\ResourceGenerator\StrategyInterface` and all of its implementations. The `ExtractCollectionTrait` and `ExtractInstanceTrait` implementations were also updated, however, so if you were using one of those, your extensions will likely be insulated.

- [#19](https://github.com/mezzio/mezzio-hal/pull/19) updates `ResourceGenerator` to implement the new `ResourceGeneratorInterface`.

### Removed

- [#27](https://github.com/mezzio/mezzio-hal/pull/27) removes support for the `route_identifier_placeholder` configuration setting from `RouteBasedResourceMetadataFactory`; users should use the `identifiers_to_placeholders_mapping` configuration instead to map the resource identifier to the route placeholder.

- [#27](https://github.com/mezzio/mezzio-hal/pull/27) removes the `$routeIdentifierPlaceholder` property and constructor argument from `RouteBasedResourceMetadata`, as well as the `getRouteIdentifierPlaceholder()` method. Users should use the `$identiersToPlaceholdersMapping` argument instead to map resource identifiers to the appropriate route placeholder.


-----

### Release Notes for [2.0.0](https://github.com/mezzio/mezzio-hal/milestone/2)



### 2.0.0

- Total issues resolved: **6**
- Total pull requests resolved: **6**
- Total contributors: **5**

#### Documentation

 - [30: V2 documentation](https://github.com/mezzio/mezzio-hal/pull/30) thanks to @weierophinney

#### BC Break,Enhancement

 - [29: Provide Psalm integration](https://github.com/mezzio/mezzio-hal/pull/29) thanks to @weierophinney

#### Enhancement

 - [28: Update to laminas-coding-standard 2.1 series](https://github.com/mezzio/mezzio-hal/pull/28) thanks to @weierophinney
 - [24: Update to laminas-coding-standard 2.0.0+](https://github.com/mezzio/mezzio-hal/issues/24) thanks to @weierophinney

#### BC Break,Feature Removal

 - [27: Remove deprecated functionality in preparation for 2.0.0](https://github.com/mezzio/mezzio-hal/pull/27) thanks to @weierophinney

#### Documentation,Enhancement

 - [25: Version documentation for v2.0](https://github.com/mezzio/mezzio-hal/issues/25) thanks to @weierophinney

#### BC Break,Bug,Enhancement

 - [22: Unable to convert self-referring instances to resources](https://github.com/mezzio/mezzio-hal/pull/22) thanks to @tobias-trozowski and @weierophinney

#### BC Break,Documentation Needed,Enhancement

 - [19: ResourceGenerator implements an interface to allow composition](https://github.com/mezzio/mezzio-hal/pull/19) thanks to @jguittard

#### Feature Request

 - [9: Resource Generator abstract class](https://github.com/mezzio/mezzio-hal/issues/9) thanks to @bkilinc

## 1.4.0 - 2020-12-21

### Added

- [#21](https://github.com/mezzio/mezzio-hal/pull/21) adds support for PHP 8.0.

- [#13](https://github.com/mezzio/mezzio-hal/pull/13) adds a new configuration key, `identifiers_to_placeholders_mapping`, for use with the `RouteBasedResourceMetadata`. The setting is an associative array mapping resource properties/identifiers to the route placeholders they should fill. The setting corresponds to an optional seventh argument to the `RouteBasedResourceMetadata` class, and can be used to replace and extend the `$routeIdentifierPlaceholder` argument and corresponding `route_identifier_placeholder` configuration; it is more flexible, as it allows more than a single mapping to occur.

### Changed

- [#16](https://github.com/mezzio/mezzio-hal/pull/16) changes the behavior when route-based resource links are generated. It now passes all scalar properties of the resource as route parameters, allowing the resource to fill in properties required by the route, without the need of seeding metadata. As an example, if you defined the route `/group/:group_id/:user_id`, and `:user_id` is the resource identifier, and your resource also defines `group_id`, the `group_id` value will fill in the associated value in the generated URI.

### Deprecated

- [#13](https://github.com/mezzio/mezzio-hal/pull/13) deprecates both the `RouteBasedResourceMetadata` class `$routeIdentifierPlaceholder` argument and related `route_identifier_placeholder` setting. Please update your code to use the new `$identifiersToPlaceholders` argument and related `identifiers_to_placeholders_mapping` configuration instead. The argument and configuration key will be removed in version 2.0.0.

### Removed

- [#21](https://github.com/mezzio/mezzio-hal/pull/21) removes support for PHP versions prior to 7.3.

-----

### Release Notes for [1.4.0](https://github.com/mezzio/mezzio-hal/milestone/1)

### 1.4.0

- Total issues resolved: **1**
- Total pull requests resolved: **3**
- Total contributors: **3**

#### Enhancement

- [21: PHP 8 Support](https://github.com/mezzio/mezzio-hal/pull/21) thanks to @agustingomes
- [16: Make all entity keys available as route parameters](https://github.com/mezzio/mezzio-hal/pull/16) thanks to @arstom
- [14: Fast route with two placeholders](https://github.com/mezzio/mezzio-hal/issues/14) thanks to @arstom
- [13: Added an entity properties to route placeholders mapping](https://github.com/mezzio/mezzio-hal/pull/13) thanks to @corentin-larose

## 1.3.1 - 2019-02-11

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#56](https://github.com/zendframework/zend-expressive-hal/pull/56) fixes an issue calculating the offset when generating a paginated Doctrine collection.

## 1.3.0 - 2019-02-06

### Added

- [zendframework/zend-expressive-hal#55](https://github.com/zendframework/zend-expressive-hal/pull/55) adds the ability to generate paginated HAL collections from
  `Doctrine\ORM\Tools\Pagination\Paginator` instances.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.0 - 2018-12-11

### Added

- [zendframework/zend-expressive-hal#51](https://github.com/zendframework/zend-expressive-hal/pull/51) adds support for laminas-hydrator version 3 releases. You may continue to use
  version 2 releases as well.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.1 - 2018-12-11

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#50](https://github.com/zendframework/zend-expressive-hal/pull/50) fixes the `Halresource` constructor documentation of the `$embedded`
  argument to correctly be an array of `HalResource` arrays (and not just an
  array of `HalResource` instances).

- [zendframework/zend-expressive-hal#41](https://github.com/zendframework/zend-expressive-hal/pull/41) fixes how `null` values in resources are handled when rendering as XML.
  Previously, these would lead to an `InvalidResourceValueException`; now they
  are rendered as content-less tags.

## 1.1.0 - 2018-06-05

### Added

- [zendframework/zend-expressive-hal#39](https://github.com/zendframework/zend-expressive-hal/pull/39) adds a cookbook recipe detailing how to create a fully contained, path-segregated
  module, complete with its own router, capable of generating HAL resources.

### Changed

- [zendframework/zend-expressive-hal#39](https://github.com/zendframework/zend-expressive-hal/pull/39) updates `LinkGeneratorFactory` to allow passing an alternate service name to use when
  retrieving the `LinkGenerator\UriGeneratorInterface` dependency.

- [zendframework/zend-expressive-hal#39](https://github.com/zendframework/zend-expressive-hal/pull/39) updates `ResourceGeneratorFactory` to allow passing an alternate service name to use when
  retrieving the `LinkGenerator` dependency.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2018-04-04

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#37](https://github.com/zendframework/zend-expressive-hal/pull/37) modifies
  `HalResource` to no longer treat empty arrays as embedded collections when
  passed via the constructor or `withElement()`. If an empty embedded collection
  is required, use `embed()` with a boolean third argument to force
  representation as an array of resources.

## 1.0.1 - 2018-03-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#36](https://github.com/zendframework/zend-expressive-hal/pull/36)
  fixes an issue whereby query string arguments were not being added to
  links generated for a resource. It now correctly merges those specified in
  metadata with those from the request when generating links.

## 1.0.0 - 2018-03-15

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-hal#31](https://github.com/zendframework/zend-expressive-hal/pull/31) changes
  the constructor signature of `Mezzio\Hal\HalResponseFactory` to read:

  ```php
  public function __construct(
      callable $responseFactory,
      Renderer\JsonRenderer $jsonRenderer = null,
      Renderer\XmlRenderer $xmlRenderer = null
  )
  ```

  Previously, the `$responseFactory` argument was a
  `Psr\Http\Message\ResponseInterface $responsePrototype`; it is now a PHP
  callable capable of producing a new, empty instance of that type.

  Additionally, the signature previously included a callable `$streamFactory`;
  this has been removed.

- [zendframework/zend-expressive-hal#31](https://github.com/zendframework/zend-expressive-hal/pull/31) updates
  the `HalResponseFactoryFactory` to follow the changes made to the
  `HalResponseFactory` constructor. It now **requires** that a
  `Psr\Http\Message\ResponseInterface` service be registered, and that the
  service resolve to a `callable` capable of producing a `ResponseInterface`
  instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.6.3 - 2018-03-12

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-hal#32](https://github.com/zendframework/zend-expressive-hal/pull/32) modifies
  `HalResponseFactoryFactory` to test if a `ResponseInterface` service instance
  is `callable` before returning it; if it is, it calls it first. This allows
  the `ResponseInterface` service to return a response _factory_ instead of an
  instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.6.2 - 2018-01-03

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-hal#27](https://github.com/zendframework/zend-expressive-hal/pull/27) modifies
  the `XmlRenderer` to raise an exception when attempting to render objects that
  are not serializable to strings.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#27](https://github.com/zendframework/zend-expressive-hal/pull/27) adds
  handling for `DateTime` and string serializable objects to the `XmlRenderer`,
  allowing them to be rendered.

## 0.6.1 - 2017-12-12

### Added

- [zendframework/zend-expressive-hal#26](https://github.com/zendframework/zend-expressive-hal/pull/26) adds
  support for the mezzio-helpers 5.0 series of releases.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.6.0 - 2017-11-07

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-hal#23](https://github.com/zendframework/zend-expressive-hal/pull/23) modifies
  how the resource generator factory adds strategies and maps metadata to
  strategies. It now adds the following factories under the
  `Mezzio\Hal\Metadata` namespace:

  - `RouteBasedCollectionMetadataFactory`
  - `RouteBasedResourceMetadataFactory`
  - `UrlBasedCollectionMetadataFactory`
  - `UrlBasedResourceMetadataFactory`

  Each implements a new `MetadataFactoryInterface` under that same namespace
  that accepts the requested metadata type name and associated metadata in order
  to create an `AbstractMetadata` instance. Metadata types are mapped to their
  factories under the `mezzio-hal.metadata-factories` key.

  Strategies are now configured as metadata => strategy class pairings under the
  `mezzio-hal.resource-generator.strategies` key.

  In both cases, defaults that mimic previous behavior are provided via the
  `ConfigProvider`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.1 - 2017-11-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#21](https://github.com/zendframework/zend-expressive-hal/pull/21) fixes the
  `LinkGeneratorFactory` to properly use the
  `Mezzio\Hal\LinkGenerator\UrlGeneratorInterface` service when
  creating and returning the `LinkGenerator` instance. (0.5.0 was incorrectly
  attempting to use the `UrlGenerator` service, which does not exist.)

## 0.5.0 - 2017-10-30

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-hal#20](https://github.com/zendframework/zend-expressive-hal/pull/20) renames
  the following interfaces and traits to have `Interface` and `Trait` suffixes,
  respectively; this was done for consistency with existing Laminas packages. (Values
  after the `:` retain the namespace, which is omitted for brevity.)

  - `Mezzio\Hal\LinkGenerator\UrlGenerator`: `UrlGeneratorInterface`
  - `Mezzio\Hal\Renderer\Renderer`: `RendererInterface`
  - `Mezzio\Hal\ResourceGenerator\Strategy`: `StrategyInterface`
  - `Mezzio\Hal\ResourceGenerator\ExtractCollection`: `ExtractCollectionTrait`
  - `Mezzio\Hal\ResourceGenerator\ExtractInstance`: `ExtractInstanceTrait`

- [zendframework/zend-expressive-hal#16](https://github.com/zendframework/zend-expressive-hal/pull/16) renames
  the various `Exception` interfaces to `ExceptionInterface`, in order to be
  consistent with other Laminas packages.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.3 - 2017-10-30

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#19](https://github.com/zendframework/zend-expressive-hal/pull/19) fixes the
  behavior of `ResourceGenerator` when nesting a collection inside another
  resource to properly nest it as an array of items, rather than a collection
  resource.

- [zendframework/zend-expressive-hal#18](https://github.com/zendframework/zend-expressive-hal/pull/18) fixes the
  return type hint of `RouteBasedResourceMetadata::setRouteParams()` to correctly
  be `void`.

- [zendframework/zend-expressive-hal#13](https://github.com/zendframework/zend-expressive-hal/pull/13) updates
  `ExtractCollection::extractPaginator()` to validate that the pagination
  parameter is within the range of pages represented by the paginator instance;
  if not, an `OutOfBoundsException` is raised.

- [zendframework/zend-expressive-hal#12](https://github.com/zendframework/zend-expressive-hal/pull/12) fixes how pagination
  metadata (`_page`, `_page_count`, `_total_items`) is represented in generated
  resources, ensuring values are cast to integers.

## 0.4.2 - 2017-09-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#7](https://github.com/zendframework/zend-expressive-hal/pull/7) fixes a number of issues in
  the various exception implementations due to failure to import classes
  referenced in typehints.

- [zendframework/zend-expressive-hal#6](https://github.com/zendframework/zend-expressive-hal/pull/6) fixes a number of docblock
  annotations to reference `HalResource` vs `Resource` (which is a reserved
  word).

## 0.4.1 - 2017-08-08

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-hal#6](https://github.com/zendframework/zend-expressive-hal/pull/6) fixes an issue with the XML
  renderer when creating resource elements that represent an array.

## 0.4.0 - 2017-08-08

### Added

- Nothing.

### Changed

- The package name was changed to "zendframework/zend-expressive-hal".
- The namespace was changed from `Hal` to `Zend\Expressive\Hal`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2017-08-07

### Added

- [zendframework/zend-expressive-hal#4](https://github.com/weierophinney/hal/pull/4) adds the ability to force
  both links and embedded resources to be rendered as collections, even if the
  given relation only contains one item.

  To force a link to be rendered as a collection, pass the attribute
  `__FORCE__COLLECTION__` with a boolean value of `true` (or use the constant
  `Link::AS_COLLECTION` to refer to the attribute name).

  To force an embedded resource to be rendered as a collection, pass a boolean
  `true` as the third argument to `embed()`. Alternately, pass an array
  containing the single resource to any of the constructor, `withElement()`, or
  `embed()`.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2017-07-13

### Added

- [zendframework/zend-expressive-hal#1](https://github.com/weierophinney/hal/pull/1) adds a `Hal\Renderer`
  subcomponent with the following:
  - `Renderer` interface
  - `JsonRenderer`, for creating JSON representations of `HalResource` instances.
  - `XmlRenderer`, for creating XML representations of `HalResource` instances.

### Changed

- [zendframework/zend-expressive-hal#1](https://github.com/weierophinney/hal/pull/1) changes `Hal\HalResponseFactory`
  to compose a `JsonRenderer` and `XmlRenderer`, instead of composing
  `$jsonFlags` and creating representations itself.

  It also makes the response prototype and the stream factory the first
  arguments, as those will be the values most often injected.

  The constructor signature is
  now:

  ```php
  public function __construct(
      Psr\Http\Message\ResponseInterface $responsePrototype = null,
      callable $streamFactory = null,
      Hal\Renderer\JsonRenderer $jsonRenderer = null,
      Hal\Renderer\XmlRenderer $xmlRenderer = null
  ) {
  ```

- [zendframework/zend-expressive-hal#1](https://github.com/weierophinney/hal/pull/1) changes `Hal\HalResponseFactoryFactory`
  to comply with the new constructor signature of `Hal\HalResponseFactory`. It
  also updates to check for `Psr\Http\Message\ResponseInterface` and
  `Psr\Http\Message\StreamInterface` services before attempting to use
  laminas-diactoros classes.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.6 - 2017-07-12

### Added

- Adds keywords to the `composer.json`
- Adds a "provides" section to the `composer.json` (provides PSR-13 implementation)
- Adds `composer.json` suggestions for:
  - PSR-11 implementation
  - laminas-paginator

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.5 - 2017-07-12

### Added

- Adds documentation; see the [doc/book/](doc/book/) tree, or browse at
  https://weierophinney.github.io/hal/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.4 - 2017-07-12

### Added

- Adds the method `templatedFromRoute()` to the `LinkGenerator` class. Acts
  exactly like `fromRoute()`, but the generated `Link` instance will have the
  `isTemplated` property toggled `true`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.3 - 2017-07-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes registration of the `MetadataMap` in the `ConfigProvider`; it was
  previously using an incorrect namespace.

## 0.1.2 - 2017-07-11

### Added

- Adds `HalResponseFactoryFactory`, a factory for generating a
  `HalResponseFactory` instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2017-07-11

### Added

- Adds the ability to inject route params and query string arguments at run-time
  to the route-based metadata instances.

  When dealing with route-based metadata, we may be dealing with
  sub-resources; in such cases, the route parameters may be derived from
  the request, and we will want to inject them at run-time.

  When dealing with collections, the query string arguments may indicate
  things such as searches, sort directions, sort columns, filters, limits,
  etc.; these will be derived from the request, and need to be injected at
  run-time.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2017-07-10

Initial Release.

### Added

- Everything.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
