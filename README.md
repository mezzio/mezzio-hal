# Hypertext Application Language (HAL) for PSR-7 Applications

[![Build Status](https://travis-ci.org/mezzio/mezzio-hal.svg?branch=master)](https://travis-ci.org/mezzio/mezzio-hal)
[![Coverage Status](https://coveralls.io/repos/github/mezzio/mezzio-hal/badge.svg?branch=master)](https://coveralls.io/github/mezzio/mezzio-hal?branch=master)

This library provides provides utilities for modeling HAL resources with links
and generating [PSR-7](http://www.php-fig.org/psr/psr-7/) responses representing
both JSON and XML serializations of them.

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
