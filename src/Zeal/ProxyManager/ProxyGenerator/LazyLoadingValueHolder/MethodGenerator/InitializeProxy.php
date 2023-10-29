<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \DI\Zeal\ProxyManager\Proxy\LazyLoadingInterface::initializeProxy}
 * for lazy loading value holder objects.
 */
class InitializeProxy extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('initializeProxy');
        $this->setReturnType('bool');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();

        $this->setBody(
            'return $this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder
            . ', $this, \'initializeProxy\', array(), $this->' . $initializer . ') || 1)'
            . ' && $this->' . $valueHolder . ' = $' . $valueHolder . ';'
        );
    }
}
