<?php

declare(strict_types=1);

namespace MezzioTest\Hal\TestAsset;

use ArrayIterator;

/**
 * @template Tk as array-key
 * @template Tv
 * @extends ArrayIterator<Tk, Tv>
 */
final class Collection extends ArrayIterator
{
}
