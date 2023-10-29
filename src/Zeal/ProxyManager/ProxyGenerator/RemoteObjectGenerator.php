<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator;

use function array_map;
use function array_merge;
use DI\Zeal\ProxyManager\Exception\InvalidProxiedClassException;
use DI\Zeal\ProxyManager\Generator\Util\ClassGeneratorUtils;
use DI\Zeal\ProxyManager\Proxy\RemoteObjectInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionMethod;

/**
 * Generator for proxies implementing {@see RemoteObjectInterface}.
 */
class RemoteObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [RemoteObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($adapter = new AdapterProperty());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) : void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    static function (ReflectionMethod $method) use ($adapter, $originalClass) : RemoteObjectMethod {
                        return RemoteObjectMethod::generateMethod(
                            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                            $adapter,
                            $originalClass
                        );
                    },
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass, $adapter),
                    new MagicGet($originalClass, $adapter),
                    new MagicSet($originalClass, $adapter),
                    new MagicIsset($originalClass, $adapter),
                    new MagicUnset($originalClass, $adapter),
                ]
            )
        );
    }
}
