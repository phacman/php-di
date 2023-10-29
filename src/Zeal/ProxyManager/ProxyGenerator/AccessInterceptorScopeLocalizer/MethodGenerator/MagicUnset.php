<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\GetMethodIfExists;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;

/**
 * Magic `__unset` method for lazy loading ghost objects.
 */
class MagicUnset extends MagicMethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct($originalClass, '__unset', [new ParameterGenerator('name')]);

        $parent = GetMethodIfExists::get($originalClass, '__unset');

        $callParent = '$returnValue = & parent::__unset($name);';

        if (! $parent) {
            $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_UNSET,
                'name',
                null,
                null,
                'returnValue',
                $originalClass
            );
        }

        $this->setBody(InterceptorGenerator::createInterceptedMethodBody(
            $callParent,
            $this,
            $prefixInterceptors,
            $suffixInterceptors,
            $parent
        ));
    }
}
