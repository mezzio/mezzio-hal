# Introduction

This component provides tools for generating Hypertext Application Language
(HAL) payloads for your APIs, in both JSON and XML formats.

At its core, it features:

- `Mezzio\Hal\Link`, a value object for describing _relational links_.
- `Mezzio\Hal\HalResource`, a value object for describing your API
  resource, its relational links, and any embedded/child resources related to
  it.

These two tools allow you to model payloads of varying complexity.

To allow providing _representations_ of these, we provide
`Mezzio\Hal\HalResponseFactory`. This factory generates a
[PSR-7](https://www.php-fig.org/psr/psr-7/) response for the provided resource,
including its links and any embedded/child resources it composes.

Creating link URIs by hand is error-prone, as URI schemas may change; most
frameworks provide route-based URI generation for this reason. To address this,
we provide `Mezzio\Hal\LinkGenerator`, and an accompanying interface,
`Mezzio\Hal\LinkGenerator\UrlGenerator`. You may use these to generate
`Link` instances that use URIs based on routes you have defined in your
application. We also ship `Mezzio\Hal\LinkGenerator\MezzioUrlGenerator`,
which provides a `UrlGenerator` implementation backed by the
mezzio-helpers package.

Finally, we recognize that most modern PHP applications use strong data
modeling, and thus API payloads need to represent PHP _objects_. To facilitate
this, we provide two components:

- `Mezzio\Hal\Metadata` is a subcomponent that allows mapping PHP
  objects to how they should be represented: Should a route be used to generate
  its self relational link? What laminas-hydrator extractor should be used to
  create a representation of the object? Does the object represent a collection?
  etc.
- `Mezzio\Hal\ResourceGenerator` consumes metadata in order to generate
  `HalResource` instances, mapping metadata to specific representation strategies.

**The purpose of the package is to automate creation of HAL payloads, including
relational links, from PHP objects.**

## Installation

Use Composer:

```bash
$ composer require mezzio/mezzio-hal
```

If you are adding this to an Mezzio application, and have the
[laminas-component-installer](https://docs.laminas.dev/laminas-component-installer/)
package installed, this will prompt you to ask if you wish to add it to your
application configuration; please do, as the package provides a number of useful
factories.

We also recommend installing [laminas-hydrator](https://docs.laminas.dev/laminas-hydrator/),
which provides facilities for extracting associative array representations of
PHP objects:

```bash
$ composer require laminas/laminas-hydrator
```

Finally, if you want to provide paginated collections, we recommend installing
[laminas-paginator](https://docs.laminas.dev/laminas-paginator/):

```bash
$ composer require laminas/laminas-paginator
```

## Quick Start

The following examples assume that you have added this package to an Mezzio
application.

### Entity and collection classes

For each of our examples, we'll assume the following class exists:

```php
namespace Api\Books;

class Book
{
    public $id;
    public $title;
    public $author;
}
```

Additionally, we'll have a class representing a paginated group of books:

```php
namespace Api\Books;

use Laminas\Paginator\Paginator;

class BookCollection extends Paginator
{
}
```

### Routes

The examples below assume that we have the following routes defined in our
application somehow:

- "book" will map to a single book by identifier: "/api/books/{id}"
- "books" will map to a queryable collection endpoint: "/api/books"

### Create metadata

In order to allow creating representations of these classes, we need to provide
the resource generator with metadata describing them. This is done via
configuration, which you could put in one of the following places:

- A new configuration file: `config/autoload/hal.global.php`.
- A `ConfigProvider` class: `Api\Books\ConfigProvider`. If you go this route,
  you will need to add an entry for this class to your `config/config.php` file.

The configuration will look like this:

```php
// Provide the following imports:
use Api\Books\Book;
use Api\Books\BookCollection;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedResourceMetadata;
use Laminas\Hydrator\ObjectProperty as ObjectPropertyHydrator;

// And include the following in your configuration:
MetadataMap::class => [
    [
        '__class__' => RouteBasedResourceMetadata::class,
        'resource_class' => Book::class,
        'route' => 'book',
        'extractor' => ObjectPropertyHydrator::class,
    ],
    [
        '__class__' => RouteBasedCollectionMetadata::class,
        'collection_class' => BookCollection::class,
        'collection_relation' => 'book',
        'route' => 'books',
    ],
],
```

### Manually creating and rendering a resource

The following request handler creates a `HalResource` with its associated links,
and then manually renders it using `Mezzio\Hal\Renderer\JsonRenderer`.
(An `XmlRenderer` is also provided, but not demonstrated here.)

We'll assume that `Api\Books\Repository` handles retrieving data from persistent
storage.

```php
namespace Api\Books\Action;

use Api\Books\Repository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\Renderer\JsonRenderer;

class BookAction implements RequestHandlerInterface
{
    /** @var JsonRenderer */
    private $renderer;

    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository,
        JsonRenderer $renderer
    ) {
        $this->repository = $repository;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $id = $request->getAttribute('id', false);
        if (! $id) {
            throw new RuntimeException('No book identifier provided', 400);
        }
        $book = $this->repository->get($id);
        $resource = new HalResource((array) $book);
        $resource = $resource->withLink(new Link('self'));
        return new TextResponse(
            $this->renderer->render($resource),
            200,
            ['Content-Type' => 'application/hal+json']
        );
    }
}
```

The `JsonRenderer` returns the JSON string representing the data and links in
the resource. The payload generated might look like the following:

```json
{
    "_links": {
        "self": { "href": "/api/books/1234" }
    },
    "id": 1234,
    "title": "Hitchhiker's Guide to the Galaxy",
    "author": "Adams, Douglas"
}
```

The above example uses no metadata, and manually creates the `HalResource`
instance. As the complexity of your objects increase, and the number of objects
you want to represent via HAL increases, you may not want to manually generate
them.

### Request handler using the ResourceGenerator and ResponseFactory

In this next example, our request handler will compose a
`Mezzio\Hal\ResourceGenerator` instance for generating a
`Mezzio\Hal\HalResource` from our objects, and a
`Mezzio\Hal\HalResponseFactory` for creating a response based on the
returned resource.

First, we'll look at a handler that displays a single book. We'll assume that
`Api\Books\Repository` handles retrieving data from persistent storage.

```php
namespace Api\Books\Action;

use Api\Books\Repository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

class BookAction implements RequestHandlerInterface
{
    /** @var Repository */
    private $repository;

    /** @var ResourceGenerator */
    private $resourceGenerator;

    /** @var HalResponseFactory */
    private $responseFactory;

    public function __construct(
        Repository $repository,
        ResourceGenerator $resourceGenerator,
        HalResponseFactory $responseFactory
    ) {
        $this->repository = $repository;
        $this->resourceGenerator = $resourceGenerator;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $id = $request->getAttribute('id', false);
        if (! $id) {
            throw new RuntimeException('No book identifier provided', 400);
        }

        /** @var \Api\Books\Book $book */
        $book = $this->repository->get($id);

        $resource = $this->resourceGenerator->fromObject($book, $request);
        return $this->responseFactory->createResponse($request, $resource);
    }
}
```

Note that the `$request` instance is passed to both the resource generator and
response factory:

- The request is used by the resource generator during link URI generation.
- The request is used by the response factory to determine if a JSON or XML
  payload should be generated.

The generated payload might look like the following:

```json
{
    "_links": {
        "self": { "href": "/api/books/1234" }
    },
    "id": 1234,
    "title": "Hitchhiker's Guide to the Galaxy",
    "author": "Adams, Douglas"
}
```

### Request handler returning a collection

Next, we'll create a request handler that returns a _collection_ of books. The
collection will be _paginated_ (assume our repository class creates a
`BookCollection` backed by an appropriate adapter), and use a query string
parameter to determine which page of results to return.

```php
namespace Api\Books\Action;

use Api\Books\Repository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

class BooksAction implements RequestHandlerInterface
{
    /** @var Repository */
    private $repository;

    /** @var ResourceGenerator */
    private $resourceGenerator;

    /** @var HalResponseFactory */
    private $responseFactory;

    public function __construct(
        Repository $repository,
        ResourceGenerator $resourceGenerator,
        HalResponseFactory $responseFactory
    ) {
        $this->repository = $repository;
        $this->resourceGenerator = $resourceGenerator;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $page = $request->getQueryParams()['page'] ?? 1;

        /** @var \Api\Books\BookCollection $books */
        $books = $this->repository->fetchAll();

        $books->setItemCountPerPage(25);
        $books->setCurrentPageNumber($page);

        $resource = $this->resourceGenerator->fromObject($books, $request);
        return $this->responseFactory->createResponse($request, $resource);
    }
}
```

Note that resource and response generation _is exactly the same_ as our previous
example! This is because the metadata map takes care of the details of
extracting the data from our value objects and generating links for us.

In this particular example, since we are using a paginator for our collection
class, we might get back something like the following:

```json
{
    "_links": {
        "self": { "href": "/api/books?page=7" },
        "first": { "href": "/api/books?page=1" },
        "prev": { "href": "/api/books?page=6" },
        "next": { "href": "/api/books?page=8" },
        "last": { "href": "/api/books?page=17" }
        "search": {
            "href": "/api/books?query={searchTerms}",
            "templated": true
        }
    },
    "_embedded": {
        "book": [
            {
                "_links": {
                    "self": { "href": "/api/books/1234" }
                }
                "id": 1234,
                "title": "Hitchhiker's Guide to the Galaxy",
                "author": "Adams, Douglas"
            },
            {
                "_links": {
                    "self": { "href": "/api/books/6789" }
                }
                "id": 6789,
                "title": "Ancillary Justice",
                "author": "Leckie, Ann"
            },
            /* ... */
        ]
    },
    "_page": 7,
    "_per_page": 25,
    "_total": 407
}
```

## Next steps

The above examples demonstrate setting up your application to generate and
return HAL resources. In the following chapters, we'll cover:

- what HAL is, in depth.
- the `HalResource` and `Link` classes, so you can create your own custom
  resources.
- the `MetadataMap` and how to both interact with it manually as well as
  configure it. We'll also cover creating custom metadata types.
- The `ResourceGenerator`, and how you can map metadata types to strategies that
  generate representations.
