<?php

declare(strict_types=1);

namespace DI;

use DI\Zeal\Psr\Container\ContainerExceptionInterface;
use Exception;

/**
 * Exception for the Container.
 */
class DependencyException extends Exception implements ContainerExceptionInterface
{
}
