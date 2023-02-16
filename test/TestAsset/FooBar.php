<?php // phpcs:disable

namespace MezzioTest\Hal\TestAsset;

class FooBar
{
    public mixed $id = null;
    public mixed $foo = null;
    public mixed $bar = null;
    public Collection|null $children = null;
}
