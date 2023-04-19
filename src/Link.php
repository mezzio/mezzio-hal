<?php

declare(strict_types=1);

namespace Mezzio\Hal;

use InvalidArgumentException;
use Psr\Link\EvolvableLinkInterface;

use function array_filter;
use function array_reduce;
use function gettype;
use function in_array;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use function sprintf;

class Link implements EvolvableLinkInterface
{
    public const AS_COLLECTION = '__FORCE_COLLECTION__';

    /** @var array */
    private $attributes;

    /** @var string[] Link relation types */
    private $relations;

    /** @var string */
    private $uri;

    /** @var bool Whether or not the link is templated */
    private $isTemplated;

    /**
     * @param string|string[] $relation One or more relations represented by this link.
     * @param array $attributes
     * @throws InvalidArgumentException If $relation is neither a string nor an array.
     * @throws InvalidArgumentException If an array $relation is provided, but one or
     *     more values is not a string.
     */
    public function __construct($relation, string $uri = '', bool $isTemplated = false, array $attributes = [])
    {
        $this->relations   = $this->validateRelation($relation);
        $this->uri         = is_string($uri) ? $uri : (string) $uri;
        $this->isTemplated = $isTemplated;
        $this->attributes  = $this->validateAttributes($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function getHref()
    {
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function isTemplated()
    {
        return $this->isTemplated;
    }

    /**
     * {@inheritDoc}
     */
    public function getRels()
    {
        return $this->relations;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException If $href is not a string, and not an
     *     object implementing __toString.
     */
    public function withHref($href)
    {
        if (
            ! is_string($href)
            && ! (is_object($href) && method_exists($href, '__toString'))
        ) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string URI or an object implementing __toString; received %s',
                __METHOD__,
                is_object($href) ? $href::class : gettype($href)
            ));
        }
        $new      = clone $this;
        $new->uri = (string) $href;
        return $new;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException If $rel is not a string.
     */
    public function withRel($rel)
    {
        if (! is_string($rel) || empty($rel)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a non-empty string relation type; received %s',
                __METHOD__,
                is_object($rel) ? $rel::class : gettype($rel)
            ));
        }

        if (in_array($rel, $this->relations, true)) {
            return $this;
        }

        $new              = clone $this;
        $new->relations[] = $rel;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutRel($rel)
    {
        if (! is_string($rel) || empty($rel)) {
            return $this;
        }

        if (! in_array($rel, $this->relations, true)) {
            return $this;
        }

        $new            = clone $this;
        $new->relations = array_filter($this->relations, function ($value) use ($rel) {
            return $rel !== $value;
        });
        return $new;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException If $attribute is not a string or is empty.
     * @throws InvalidArgumentException If $value is neither a scalar nor an array.
     * @throws InvalidArgumentException If $value is an array, but one or more values
     *     is not a string.
     */
    public function withAttribute($attribute, $value)
    {
        $this->validateAttributeName($attribute, __METHOD__);
        $this->validateAttributeValue($value, __METHOD__);

        $new                         = clone $this;
        $new->attributes[$attribute] = $value;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($attribute)
    {
        if (! is_string($attribute) || empty($attribute)) {
            return $this;
        }

        if (! isset($this->attributes[$attribute])) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);
        return $new;
    }

    /**
     * @param mixed $name
     * @throws InvalidArgumentException If $attribute is not a string or is empty.
     */
    private function validateAttributeName($name, string $context): void
    {
        if (! is_string($name) || empty($name)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects the $name argument to be a non-empty string; received %s',
                $context,
                is_object($name) ? $name::class : gettype($name)
            ));
        }
    }

    /**
     * @param mixed $value
     * @throws InvalidArgumentException If $value is neither a scalar, an array nor null.
     * @throws InvalidArgumentException If $value is an array, but one or more values is not a string.
     */
    private function validateAttributeValue($value, string $context): void
    {
        if (! is_scalar($value) && ! is_array($value) && $value !== null) {
            throw new InvalidArgumentException(sprintf(
                '%s expects the $value to be a PHP primitive, an array of strings or null; received %s',
                $context,
                is_object($value) ? $value::class : gettype($value)
            ));
        }

        if (
            is_array($value) && array_reduce($value, function ($isInvalid, $value) {
                return $isInvalid || ! is_string($value);
            }, false)
        ) {
            throw new InvalidArgumentException(sprintf(
                '%s expects $value to contain an array of strings; one or more values was not a string',
                $context
            ));
        }
    }

    private function validateAttributes(array $attributes): array
    {
        foreach ($attributes as $name => $value) {
            $this->validateAttributeName($name, self::class);
            $this->validateAttributeValue($value, self::class);
        }
        return $attributes;
    }

    /**
     * @param mixed $relation
     * @return string|string[]
     * @throws InvalidArgumentException If $relation is neither a string nor an array.
     * @throws InvalidArgumentException If $relation is an array, but any given value in it is not a string.
     */
    private function validateRelation($relation)
    {
        if (! is_array($relation) && (! is_string($relation) || empty($relation))) {
            throw new InvalidArgumentException(sprintf(
                '$relation argument must be a string or array of strings; received %s',
                is_object($relation) ? $relation::class : gettype($relation)
            ));
        }

        if (
            is_array($relation) && false === array_reduce($relation, function ($isString, $value) {
                return $isString === false || is_string($value) || empty($value);
            }, true)
        ) {
            throw new InvalidArgumentException(
                'When passing an array for $relation, each value must be a non-empty string; '
                . 'one or more non-string or empty values were present'
            );
        }

        return is_string($relation) ? [$relation] : $relation;
    }
}
