<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Closure;
use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \DI\Zeal\ProxyManager\Proxy\LazyLoadingInterface::setProxyInitializer}
 * for lazy loading value holder objects.
 */
class SetProxyInitializer extends MethodGenerator
{
    /**
     * Constructor.
     */
    public function __construct(PropertyGenerator $initializerProperty)
    {
        parent::__construct(
            'setProxyInitializer',
            [(new ParameterGenerator('initializer', Closure::class))->setDefaultValue(null)],
            self::FLAG_PUBLIC,
            '$this->' . $initializerProperty->getName() . ' = $initializer;'
        );

        $this->setReturnType('void');
    }
}
