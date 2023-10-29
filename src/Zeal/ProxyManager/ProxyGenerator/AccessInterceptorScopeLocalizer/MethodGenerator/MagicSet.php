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
 * Magic `__set` for lazy loading ghost objects.
 */
class MagicSet extends MagicMethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct(
            $originalClass,
            '__set',
            [new ParameterGenerator('name'), new ParameterGenerator('value')]
        );

        $parent = GetMethodIfExists::get($originalClass, '__set');

        $callParent = '$returnValue = & parent::__set($name, $value);';

        if (! $parent) {
            $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'name',
                'value',
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
