<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;

/**
 * Magic `__isset` method for lazy loading value holder objects.
 */
class MagicIsset extends MagicMethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent = '';

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    return isset($this->' . $valueHolder . '->$name);'
                . "\n}\n\n";
        }

        $callParent .= PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_ISSET,
            'name',
            null,
            $valueHolderProperty,
            null,
            $originalClass
        );

        $this->setBody(
            '$this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder . ', $this, \'__isset\', array(\'name\' => $name), $this->'
            . $initializer . ') || 1) && $this->' . $valueHolder . ' = $' . $valueHolder . ';' . "\n\n" . $callParent
        );
    }
}
