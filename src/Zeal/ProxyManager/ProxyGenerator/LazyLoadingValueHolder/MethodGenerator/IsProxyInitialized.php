<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \DI\Zeal\ProxyManager\Proxy\LazyLoadingInterface::isProxyInitialized}
 * for lazy loading value holder objects.
 */
class IsProxyInitialized extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('isProxyInitialized');
        $this->setReturnType('bool');
        $this->setBody('return null !== $this->' . $valueHolderProperty->getName() . ';');
    }
}
