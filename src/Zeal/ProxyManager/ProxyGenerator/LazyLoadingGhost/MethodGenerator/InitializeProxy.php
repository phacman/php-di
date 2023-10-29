<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator as LaminasMethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \DI\Zeal\ProxyManager\Proxy\LazyLoadingInterface::initializeProxy}
 * for lazy loading ghost objects.
 */
class InitializeProxy extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, LaminasMethodGenerator $callInitializer)
    {
        parent::__construct('initializeProxy');
        $this->setReturnType('bool');

        $this->setBody(
            'return $this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'initializeProxy\', []);'
        );
    }
}
