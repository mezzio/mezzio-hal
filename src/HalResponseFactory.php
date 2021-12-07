<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use Mezzio\Hal\Response\CallableResponseFactoryDecorator;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function is_callable;
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
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /** @var Renderer\XmlRenderer */
    private $xmlRenderer;

    /**
     * @param (callable():ResponseInterface)|ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        $responseFactory,
        ?Renderer\JsonRenderer $jsonRenderer = null,
        ?Renderer\XmlRenderer $xmlRenderer = null
    ) {
        if (is_callable($responseFactory)) {
            // Ensures type safety of the composed factory
            $responseFactory = new CallableResponseFactoryDecorator(
                static function () use ($responseFactory): ResponseInterface {
                    return $responseFactory();
                }
            );
        }

        $this->responseFactory = $responseFactory;
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

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($renderer->render($resource));
        return $response->withHeader('Content-Type', $mediaType);
    }
}
