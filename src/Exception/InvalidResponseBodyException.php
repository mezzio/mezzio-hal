<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Exception;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class InvalidResponseBodyException extends RuntimeException implements ExceptionInterface
{
    public static function forIncorrectStreamType() : self
    {
        return new self(sprintf(
            'The factory for generating a HAL response body stream did not return a %s instance',
            StreamInterface::class
        ));
    }

    public static function forNonWritableStream() : self
    {
        return new self(
            'The factory for generating a HAL response body stream returned a non-writable stream'
        );
    }
}
