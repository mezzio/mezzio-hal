<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Psr\Http\Message\ServerRequestInterface;

class LinkGenerator
{
    /** @var LinkGenerator\UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(LinkGenerator\UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

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
