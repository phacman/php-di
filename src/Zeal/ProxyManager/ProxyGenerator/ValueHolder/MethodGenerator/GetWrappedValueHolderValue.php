<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \DI\Zeal\ProxyManager\Proxy\ValueHolderInterface::getWrappedValueHolderValue}
 * for lazy loading value holder objects.
 */
class GetWrappedValueHolderValue extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('getWrappedValueHolderValue');
        $this->setBody('return $this->' . $valueHolderProperty->getName() . ';');
    }
}
