<?php

declare(strict_types=1);

namespace MezzioTest\Hal\Metadata;

use Mezzio\Hal\Exception\ExceptionInterface as HalExceptionInterface;
use Mezzio\Hal\Metadata\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;

use function assert;
use function basename;
use function glob;
use function is_a;
use function strrpos;
use function substr;

class ExceptionTest extends TestCase
{
    public function testExceptionInterfaceExtendsHalExceptionInterface(): void
    {
        self::assertTrue(is_a(ExceptionInterface::class, HalExceptionInterface::class, true));
    }

    /** @return iterable<string, array{0: string}> */
    public function exception(): iterable
    {
        $pos = strrpos(ExceptionInterface::class, '\\');
        assert($pos !== false);
        $namespace = substr(ExceptionInterface::class, 0, $pos + 1);

        $exceptions = glob(__DIR__ . '/../../src/Metadata/Exception/*.php');
        foreach ($exceptions as $exception) {
            $class = substr(basename($exception), 0, -4);

            yield $class => [$namespace . $class];
        }
    }

    /**
     * @dataProvider exception
     */
    public function testExceptionIsInstanceOfExceptionInterface(string $exception): void
    {
        self::assertStringContainsString('Exception', $exception);
        self::assertTrue(is_a($exception, ExceptionInterface::class, true));
    }
}
