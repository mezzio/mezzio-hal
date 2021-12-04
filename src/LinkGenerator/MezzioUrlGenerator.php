<?php

declare(strict_types=1);

namespace Mezzio\Hal\LinkGenerator;

use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ServerRequestInterface;

class MezzioUrlGenerator implements UrlGeneratorInterface
{
    /** @var null|ServerUrlHelper */
    private $serverUrlHelper;

    /** @var UrlHelper */
    private $urlHelper;

    public function __construct(UrlHelper $urlHelper, ?ServerUrlHelper $serverUrlHelper = null)
    {
        $this->urlHelper       = $urlHelper;
        $this->serverUrlHelper = $serverUrlHelper;
    }

    public function generate(
        ServerRequestInterface $request,
        string $routeName,
        array $routeParams = [],
        array $queryParams = []
    ): string {
        $path = $this->urlHelper->generate($routeName, $routeParams, $queryParams);

        if (! $this->serverUrlHelper) {
            return $path;
        }

        $serverUrlHelper = clone $this->serverUrlHelper;
        $serverUrlHelper->setUri($request->getUri());
        return $serverUrlHelper($path);
    }
}
