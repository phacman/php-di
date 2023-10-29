<?php

declare(strict_types=1);

namespace DI\Definition\Exception;

use DI\Definition\Definition;
use DI\Zeal\Psr\Container\ContainerExceptionInterface;
use Exception;
use const PHP_EOL;

/**
 * Invalid DI definitions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InvalidDefinition extends Exception implements ContainerExceptionInterface
{
    public static function create(Definition $definition, string $message, Exception $previous = null) : self
    {
        return new self(sprintf(
            '%s' . PHP_EOL . 'Full definition:' . PHP_EOL . '%s',
            $message,
            (string) $definition
        ), 0, $previous);
    }
}
