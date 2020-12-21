<?php

namespace MezzioTest\Hal;

use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;

trait PHPUnitDeprecatedAssertions
{
    /**
     * Asserts that a variable is of a given type.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3369
     */
    public static function assertInternalType(string $expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType($expected),
            $message
        );
    }

    /**
     * Asserts that a static attribute of a class or an attribute of an object
     * is empty.
     *
     * @param object|string $haystackClassOrObject
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function assertAttributeEmpty(
        string $haystackAttributeName,
        $haystackClassOrObject,
        string $message = ''
    ): void {
        static::assertEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param object|string $classOrObject
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function assertAttributeInstanceOf(
        string $expected,
        string $attributeName,
        $classOrObject,
        string $message = ''
    ): void {
        static::assertInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    /**
     * Asserts that a variable and an attribute of an object have the same type
     * and value.
     *
     * @param object|string $actualClassOrObject
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function assertAttributeSame(
        $expected,
        string $actualAttributeName,
        $actualClassOrObject,
        string $message = ''
    ): void {
        static::assertSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    /**
     * Returns the value of an attribute of a class or an object.
     * This also works for attributes that are declared protected or private.
     *
     * @param object|string $classOrObject
     *
     * @throws Exception
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function readAttribute($classOrObject, string $attributeName)
    {
        if (! self::isValidClassAttributeName($attributeName)) {
            throw self::invalidArgument(2, 'valid attribute name');
        }

        if (\is_string($classOrObject)) {
            if (! \class_exists($classOrObject)) {
                throw self::invalidArgument(
                    1,
                    'class name'
                );
            }

            return static::getStaticAttribute(
                $classOrObject,
                $attributeName
            );
        }

        if (\is_object($classOrObject)) {
            return static::getObjectAttribute(
                $classOrObject,
                $attributeName
            );
        }

        throw self::invalidArgument(
            1,
            'class name or object'
        );
    }

    /**
     * Returns the value of a static attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @throws Exception
     * @throws ReflectionException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function getStaticAttribute(string $className, string $attributeName)
    {
        if (! \class_exists($className)) {
            throw self::invalidArgument(1, 'class name');
        }

        if (! self::isValidClassAttributeName($attributeName)) {
            throw self::invalidArgument(2, 'valid attribute name');
        }

        $class = new ReflectionClass($className);

        while ($class) {
            $attributes = $class->getStaticProperties();

            if (\array_key_exists($attributeName, $attributes)) {
                return $attributes[$attributeName];
            }

            $class = $class->getParentClass();
        }

        throw new Exception(
            \sprintf(
                'Attribute "%s" not found in class.',
                $attributeName
            )
        );
    }

    /**
     * Returns the value of an object's attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param object $object
     *
     * @throws Exception
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function getObjectAttribute($object, string $attributeName)
    {
        if (! \is_object($object)) {
            throw self::invalidArgument(1, 'object');
        }

        if (! self::isValidClassAttributeName($attributeName)) {
            throw self::invalidArgument(2, 'valid attribute name');
        }

        try {
            $reflector = new ReflectionObject($object);

            do {
                try {
                    $attribute = $reflector->getProperty($attributeName);

                    if (! $attribute || $attribute->isPublic()) {
                        return $object->$attributeName;
                    }

                    $attribute->setAccessible(true);
                    $value = $attribute->getValue($object);
                    $attribute->setAccessible(false);

                    return $value;
                } catch (ReflectionException $e) {
                }
            } while ($reflector = $reflector->getParentClass());
        } catch (ReflectionException $e) {
        }

        throw new Exception(
            \sprintf(
                'Attribute "%s" not found in object.',
                $attributeName
            )
        );
    }

    private static function isValidClassAttributeName(string $attributeName): bool
    {
        return \preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName);
    }

    private static function invalidArgument(int $argument, string $type, $value = null): Exception
    {
        $stack = \debug_backtrace();

        return new Exception(
            \sprintf(
                'Argument #%d%sof %s::%s() must be a %s',
                $argument,
                $value !== null ? ' (' . \gettype($value) . '#' . $value . ')' : ' (No Value) ',
                $stack[1]['class'],
                $stack[1]['function'],
                $type
            )
        );
    }
}
