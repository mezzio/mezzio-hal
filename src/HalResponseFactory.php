<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function strstr;

class HalResponseFactory
{
    /**
     * @var string Default mediatype to use as the base Content-Type, minus the format.
     */
    public const DEFAULT_CONTENT_TYPE = 'application/hal';

    /**
     * @var string[]
     */
    public const NEGOTIATION_PRIORITIES = [
        'application/json',
        'application/*+json',
        'application/xml',
        'application/*+xml',
    ];

    /** @var Renderer\JsonRenderer */
    private $jsonRenderer;

    /**
     * A callable capable of producing an empty ResponseInterface instance.
     *
     * @var callable
     */
    private $responseFactory;

    /** @var Renderer\XmlRenderer */
    private $xmlRenderer;

    public function __construct(
        callable $responseFactory,
        ?Renderer\JsonRenderer $jsonRenderer = null,
        ?Renderer\XmlRenderer $xmlRenderer = null
    ) {
        // Ensures type safety of the composed factory
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
        $this->jsonRenderer    = $jsonRenderer ?: new Renderer\JsonRenderer();
        $this->xmlRenderer     = $xmlRenderer ?: new Renderer\XmlRenderer();
    }

    public function createResponse(
        ServerRequestInterface $request,
        HalResource $resource,
        string $mediaType = self::DEFAULT_CONTENT_TYPE
    ): ResponseInterface {
        $accept      = $request->getHeaderLine('Accept') ?: '*/*';
        $matchedType = (new Negotiator())->getBest($accept, self::NEGOTIATION_PRIORITIES);

        switch (true) {
            case $matchedType && strstr($matchedType->getValue(), 'json'):
                $renderer   = $this->jsonRenderer;
                $mediaType .= '+json';
                break;
            case ! $matchedType:
                // fall-through
            default:
                $renderer   = $this->xmlRenderer;
                $mediaType .= '+xml';
                break;
        }

        $response = ($this->responseFactory)();
        $response->getBody()->write($renderer->render($resource));
        return $response->withHeader('Content-Type', $mediaType);
    }
}
