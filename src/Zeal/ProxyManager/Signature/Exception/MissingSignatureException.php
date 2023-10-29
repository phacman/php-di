<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Signature\Exception;

use function count;
use ReflectionClass;
use function sprintf;
use UnexpectedValueException;

/**
 * Exception for no found signatures.
 */
class MissingSignatureException extends UnexpectedValueException implements ExceptionInterface
{
    /** @param mixed[] $parameters */
    public static function fromMissingSignature(ReflectionClass $class, array $parameters, string $expected) : self
    {
        return new self(sprintf(
            'No signature found for class "%s", expected signature "%s" for %d parameters',
            $class->getName(),
            $expected,
            count($parameters)
        ));
    }
}
