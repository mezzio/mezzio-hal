<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

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
