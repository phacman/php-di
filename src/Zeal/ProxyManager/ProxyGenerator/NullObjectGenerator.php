<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator;

use DI\Zeal\ProxyManager\Exception\InvalidProxiedClassException;
use DI\Zeal\ProxyManager\Generator\Util\ClassGeneratorUtils;
use DI\Zeal\ProxyManager\Proxy\NullObjectInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use DI\Zeal\ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use DI\Zeal\ProxyManager\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;

/**
 * Generator for proxies implementing {@see NullObjectInterface}.
 */
class NullObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator) : void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [NullObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass, []) as $method) {
            $classGenerator->addMethodFromGenerator(
                NullObjectMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }

        ClassGeneratorUtils::addMethodIfNotFinal(
            $originalClass,
            $classGenerator,
            new StaticProxyConstructor($originalClass)
        );
    }
}
