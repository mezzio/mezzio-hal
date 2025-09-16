<?php

declare(strict_types=1);

namespace Mezzio\Hal\ResourceGenerator\Exception;

use OutOfBoundsException as BaseOutOfBoundsException;

/** @final */
class OutOfBoundsException extends BaseOutOfBoundsException implements ExceptionInterface
{
}
