<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\NullObject\MethodGenerator;

use function array_map;
use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\Properties;
use function implode;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

/**
 * The `staticProxyConstructor` implementation for null object proxies.
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @param ReflectionClass $originalClass Reflection of the class to proxy
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct('staticProxyConstructor', [], self::FLAG_PUBLIC | self::FLAG_STATIC);

        $nullableProperties = array_map(
            static function (ReflectionProperty $publicProperty) : string {
                return '$instance->' . $publicProperty->getName() . ' = null;';
            },
            Properties::fromReflectionClass($originalClass)
                ->onlyNullableProperties()
                ->getPublicProperties()
        );

        $this->setReturnType($originalClass->getName());
        $this->setDocBlock('Constructor for null object initialization');
        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?? new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance   = $reflection->newInstanceWithoutConstructor();' . "\n\n"
            . ($nullableProperties ? implode("\n", $nullableProperties) . "\n\n" : '')
            . 'return $instance;'
        );
    }
}
