<?php

declare(strict_types=1);

namespace Mezzio\Hal\LinkGenerator;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface describing a class that can generate a URL for Link HREFs.
 */
interface UrlGeneratorInterface
{
    /**
     * Generate a URL for use as the HREF of a link.
     *
     * - The request is provided, to allow the implementation to pull any
     *   request-specific items it may need (e.g., results of routing, original
     *   URI for purposes of generating a fully-qualified URI, etc.).
     *
     * - `$routeParams` are any replacements to make in the route string.
     *
     * - `$queryParams` are any query string parameters to include in the
     *   generated URL.
     *
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $queryParams
     */
    public function generate(
        ServerRequestInterface $request,
        string $routeName,
        array $routeParams = [],
        array $queryParams = []
    ): string;
}
