<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator;

use function array_map;
use function array_merge;
use DI\Zeal\ProxyManager\Exception\InvalidProxiedClassException;
use DI\Zeal\ProxyManager\Generator\Util\ClassGeneratorUtils;
use DI\Zeal\ProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicClone;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor;
use DI\Zeal\ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use DI\Zeal\ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\Properties;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep;
use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionMethod;

/**
 * Generator for proxies implementing {@see \DI\Zeal\ProxyManager\Proxy\ValueHolderInterface}
 * and {@see \DI\Zeal\ProxyManager\Proxy\AccessInterceptorInterface}.
 */
class AccessInterceptorValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws InvalidProxiedClassException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));
        $interfaces = [AccessInterceptorValueHolderInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) : void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors, $valueHolder),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new StaticProxyConstructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new GetWrappedValueHolderValue($valueHolder),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicSet(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $valueHolder,
                        $prefixInterceptors,
                        $suffixInterceptors,
                        $publicProperties
                    ),
                    new MagicClone($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $valueHolder),
                    new MagicWakeup($originalClass),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixes,
        MethodSuffixInterceptors $suffixes,
        ValueHolderProperty $valueHolder
    ) : callable {
        return static function (ReflectionMethod $method) use ($prefixes, $suffixes, $valueHolder) : InterceptedMethod {
            return InterceptedMethod::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $valueHolder,
                $prefixes,
                $suffixes
            );
        };
    }
}
