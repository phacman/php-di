<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\GetMethodIfExists;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;

/**
 * Magic `__get` for method interceptor value holder objects.
 */
class MagicGet extends MagicMethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolder,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__get', [new ParameterGenerator('name')]);

        $parent = GetMethodIfExists::get($originalClass, '__get');
        $valueHolderName = $valueHolder->getName();

        $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_GET,
            'name',
            null,
            $valueHolder,
            'returnValue',
            $originalClass
        );

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    $returnValue = & $this->' . $valueHolderName . '->$name;'
                . "\n} else {\n    " . $callParent . "\n}\n\n";
        }

        $this->setBody(InterceptorGenerator::createInterceptedMethodBody(
            $callParent,
            $this,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $parent
        ));
    }
}
