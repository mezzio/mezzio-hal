<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata\Exception;

use Mezzio\Hal\Metadata\AbstractMetadata;
use Mezzio\Hal\Metadata\MetadataFactoryInterface;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\MetadataMapFactory;
use RuntimeException;

use function gettype;
use function implode;
use function is_object;
use function is_string;
use function sprintf;

class InvalidConfigException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param mixed $config
     */
    public static function dueToNonArray($config): self
    {
        return new self(sprintf(
            'Invalid %s configuration; expected an array, but received %s',
            MetadataMap::class,
            is_object($config) ? $config::class : gettype($config)
        ));
    }

    /**
     * @param mixed $metadata
     */
    public static function dueToNonArrayMetadata($metadata): self
    {
        return new self(sprintf(
            'Invalid %s metadata item configuration; expected an array, but received %s',
            MetadataMap::class,
            is_object($metadata) ? $metadata::class : gettype($metadata)
        ));
    }

    public static function dueToMissingMetadataClass(): self
    {
        return new self('Unable to generate metadata; missing "__class__" element');
    }

    /**
     * @param mixed $class
     */
    public static function dueToInvalidMetadataClass($class): self
    {
        $className = $class;
        if (! is_string($className)) {
            $className = is_object($class) ? $class::class : gettype($class);
        }
        return new self(sprintf(
            'Invalid metadata class provided: %s is not a class name',
            $className
        ));
    }

    public static function dueToNonMetadataClass(string $class): self
    {
        return new self(sprintf(
            '%s is not a valid metadata class; does not extend %s',
            $class,
            AbstractMetadata::class
        ));
    }

    public static function dueToInvalidMetadataFactoryClass(string $class): self
    {
        return new self(sprintf(
            '%s is not a valid metadata factory class; does not implement %s',
            $class,
            MetadataFactoryInterface::class
        ));
    }

    public static function dueToUnrecognizedMetadataClass(string $class): self
    {
        return new self(sprintf(
            '%s does not know how to construct a %s instance; please provide a '
            . 'factory in your configuration',
            MetadataMapFactory::class,
            $class
        ));
    }

    /** @param string[] $requiredKeys */
    public static function dueToMissingMetadata(string $type, array $requiredKeys): self
    {
        return new self(sprintf(
            'Unable to create HAL metadata of type %s; one or more of the '
            . 'following keys were missing: %s',
            $type,
            implode(', ', $requiredKeys)
        ));
    }

    public static function dueToConflictingRouteIdentifierPlaceholder(
        string $resourceIdentifier,
        string $routeIdentifierPlaceholder,
        string $routeIdentifierPlaceholderFromMapping
    ): self {
        return new self(sprintf(
            'You have specified both a "$routeIdentifierPlaceholder" value ("%s") and provided one for the'
            . ' "%s" (resourceIdentifier) key of the $identifiersToPlaceholdersMapping" (with value "%s"),'
            . ' creating a conflict. Set the correct value in the "$identifiersToPlaceholdersMapping",'
            . ' and set the "$routeIdentifierPlaceholder" value to "id" to correct the issue.',
            $routeIdentifierPlaceholder,
            $resourceIdentifier,
            $routeIdentifierPlaceholderFromMapping
        ));
    }
}
