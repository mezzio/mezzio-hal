# Hypertext Application Language (HAL) for PSR-7 Applications

[![Build Status](https://github.com/mezzio/mezzio-hal/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/mezzio/mezzio-hal/actions/workflows/continuous-integration.yml)

This library provides utilities for modeling HAL resources with links and generating [PSR-7](https://www.php-fig.org/psr/psr-7/) responses representing both JSON and XML serializations of them.
(The library consumes [PSR-17](https://www.php-fig.org/psr/psr-17/) `ResponseFactoryInterface` implementations in order to provide HAL response instances.)

## Installation

Run the following to install this library:

```bash
$ composer require mezzio/mezzio-hal
```

## Documentation

Documentation is [in the doc tree](docs/book/), and can be compiled using [mkdocs](https://www.mkdocs.org):

```bash
$ mkdocs build
```

You may also [browse the documentation online](https://docs.mezzio.dev/mezzio-hal/).
