<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Exception;

use function sprintf;
use Throwable;
use UnexpectedValueException;

/**
 * Exception for non writable files.
 */
class FileNotWritableException extends UnexpectedValueException implements ExceptionInterface
{
    /**
     * @deprecated
     */
    public static function fromInvalidMoveOperation(string $fromPath, string $toPath) : self
    {
        return new self(sprintf(
            'Could not move file "%s" to location "%s": '
            . 'either the source file is not readable, or the destination is not writable',
            $fromPath,
            $toPath
        ));
    }

    /**
     * @deprecated
     */
    public static function fromNotWritableDirectory(string $directory) : self
    {
        return new self(sprintf(
            'Could not create temp file in directory "%s" '
            . 'either the directory does not exist, or it is not writable',
            $directory
        ));
    }

    public static function fromPrevious(Throwable $previous) : self
    {
        return new self($previous->getMessage(), 0, $previous);
    }
}
