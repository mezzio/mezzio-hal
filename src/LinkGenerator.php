<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Mezzio\Hal\LinkGenerator\UrlGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

class LinkGenerator
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $attributes
     */
    public function fromRoute(
        string $relation,
        ServerRequestInterface $request,
        string $routeName,
        array $routeParams = [],
        array $queryParams = [],
        array $attributes = []
    ): Link {
        return new Link($relation, $this->urlGenerator->generate(
            $request,
            $routeName,
            $routeParams,
            $queryParams
        ), false, $attributes);
    }

    /**
     * Creates a templated link
     *
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $attributes
     */
    public function templatedFromRoute(
        string $relation,
        ServerRequestInterface $request,
        string $routeName,
        array $routeParams = [],
        array $queryParams = [],
        array $attributes = []
    ): Link {
        return new Link($relation, $this->urlGenerator->generate(
            $request,
            $routeName,
            $routeParams,
            $queryParams
        ), true, $attributes);
    }
}
