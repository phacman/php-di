<?php

declare(strict_types=1);

namespace DI;

use DI\Zeal\Psr\Container\NotFoundExceptionInterface;
use Exception;

/**
 * Exception thrown when a class or a value is not found in the container.
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
