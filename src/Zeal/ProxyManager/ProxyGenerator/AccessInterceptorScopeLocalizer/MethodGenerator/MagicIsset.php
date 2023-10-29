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
 * Magic `__isset` method for lazy loading ghost objects.
 */
class MagicIsset extends MagicMethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $parent = GetMethodIfExists::get($originalClass, '__isset');

        $callParent = '$returnValue = & parent::__isset($name);';

        if (! $parent) {
            $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_ISSET,
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
