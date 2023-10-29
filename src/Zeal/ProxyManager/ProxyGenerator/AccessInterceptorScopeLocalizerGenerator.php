<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator;

use function array_map;
use function array_merge;
use DI\Zeal\ProxyManager\Exception\InvalidProxiedClassException;
use DI\Zeal\ProxyManager\Generator\Util\ClassGeneratorUtils;
use DI\Zeal\ProxyManager\Proxy\AccessInterceptorInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicGet;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor;
use DI\Zeal\ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionMethod;

/**
 * Generator for proxies implementing {@see \DI\Zeal\ProxyManager\Proxy\ValueHolderInterface}
 * and localizing scope of the proxied object at instantiation.
 *
 * {@inheritDoc}
 */
class AccessInterceptorScopeLocalizerGenerator implements ProxyGeneratorInterface
{
    /**
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws InvalidProxiedClassException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([AccessInterceptorInterface::class]);
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) : void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors),
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset', '__clone', '__sleep']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass),
                    new BindProxyProperties($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new SetMethodPrefixInterceptor($prefixInterceptors),
                    new SetMethodSuffixInterceptor($suffixInterceptors),
                    new MagicGet($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicSet($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicIsset($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicUnset($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicSleep($originalClass, $prefixInterceptors, $suffixInterceptors),
                    new MagicClone($originalClass, $prefixInterceptors, $suffixInterceptors),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixInterceptors,
        MethodSuffixInterceptors $suffixInterceptors
    ) : callable {
        return static function (ReflectionMethod $method) use ($prefixInterceptors, $suffixInterceptors) : InterceptedMethod {
            return InterceptedMethod::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $prefixInterceptors,
                $suffixInterceptors
            );
        };
    }
}
