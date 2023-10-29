<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\Properties;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;

/**
 * Magic `__wakeup` for lazy loading value holder objects.
 */
class MagicWakeup extends MagicMethodGenerator
{
    /**
     * Constructor.
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct($originalClass, '__wakeup');

        $this->setBody(UnsetPropertiesGenerator::generateSnippet(
            Properties::fromReflectionClass($originalClass),
            'this'
        ));
    }
}
