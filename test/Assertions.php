<?php

declare(strict_types=1);

namespace MezzioTest\Hal;

use Laminas\Hydrator\ObjectProperty;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;

use function array_shift;
use function class_exists;
use function count;
use function gettype;
use function in_array;
use function is_object;
use function sprintf;
use function var_export;

// phpcs:ignore WebimpressCodingStandard.NamingConventions.Trait.Suffix
trait Assertions
{
    /**
     * @psalm-return ObjectPropertyHydrator::class|ObjectProperty::class
     */
    public static function getObjectPropertyHydratorClass(): string
    {
        return class_exists(ObjectPropertyHydrator::class)
            ? ObjectPropertyHydrator::class
            : ObjectProperty::class;
    }

    public static function getLinkByRel(string $rel, HalResource $resource): Link
    {
        $links = $resource->getLinksByRel($rel);
        self::assertIsArray($links, sprintf("Did not receive list of links for rel %s", $rel));
        self::assertCount(1, $links, sprintf(
            'Received more links than expected (expected 1; received %d) for rel %s',
            count($links),
            $rel
        ));
        return array_shift($links);
    }

    /**
     * @param mixed $actual
     */
    public static function assertLink(string $expectedRel, string $expectedHref, $actual): void
    {
        self::assertThat($actual instanceof Link, self::isTrue(), sprintf(
            'Invalid link encountered of type %s',
            is_object($actual) ? $actual::class : gettype($actual)
        ));

        self::assertThat(in_array($expectedRel, $actual->getRels(), true), self::isTrue(), sprintf(
            'Failed asserting that link has relation %s; received %s',
            $expectedRel,
            var_export($actual->getRels(), true)
        ));

        self::assertThat($expectedHref === $actual->getHref(), self::isTrue(), sprintf(
            'Failed asserting that link defines HREF %s; received %s',
            $expectedHref,
            $actual->getHref()
        ));
    }
}
