<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;

/**
 * Magic `__clone` for lazy loading value holder objects.
 */
class MagicClone extends MagicMethodGenerator
{
    /**
     * Constructor.
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty
    ) {
        parent::__construct($originalClass, '__clone');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();

        $this->setBody(
            '$this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder
            . ', $this, \'__clone\', array(), $this->' . $initializer . ') || 1)'
            . ' && $this->' . $valueHolder . ' = $' . $valueHolder . ';' . "\n\n"
            . '$this->' . $valueHolder . ' = clone $this->' . $valueHolder . ';'
        );
    }
}
