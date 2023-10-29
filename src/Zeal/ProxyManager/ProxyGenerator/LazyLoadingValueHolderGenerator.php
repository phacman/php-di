<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator;

use function array_map;
use function array_merge;
use DI\Zeal\ProxyManager\Exception\InvalidProxiedClassException;
use DI\Zeal\ProxyManager\Generator\Util\ClassGeneratorUtils;
use DI\Zeal\ProxyManager\Proxy\VirtualProxyInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use DI\Zeal\ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSleep;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SkipDestructor;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use DI\Zeal\ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\Properties;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use function func_get_arg;
use function func_num_args;
use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionMethod;
use function str_replace;
use function substr;

/**
 * Generator for proxies implementing {@see VirtualProxyInterface}.
 */
class LazyLoadingValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * @psalm-param array{skipDestructor?: bool, fluentSafe?: bool} $proxyOptions
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator/* , array $proxyOptions = [] */)
    {
        /** @psalm-var array{skipDestructor?: bool, fluentSafe?: bool} $proxyOptions */
        $proxyOptions = func_num_args() >= 3 ? func_get_arg(2) : [];

        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [VirtualProxyInterface::class];
        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        $skipDestructor = ($proxyOptions['skipDestructor'] ?? false) && $originalClass->hasMethod('__destruct');
        $excludedMethods = ProxiedMethodsFilter::DEFAULT_EXCLUDED;

        if ($skipDestructor) {
            $excludedMethods[] = '__destruct';
        }

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) : void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildLazyLoadingMethodInterceptor($initializer, $valueHolder, $proxyOptions['fluentSafe'] ?? false),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass, $excludedMethods)
                ),
                [
                    new StaticProxyConstructor($initializer, Properties::fromReflectionClass($originalClass)),
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new MagicGet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicSet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicIsset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicUnset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicClone($originalClass, $initializer, $valueHolder),
                    new MagicSleep($originalClass, $initializer, $valueHolder),
                    new MagicWakeup($originalClass),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $valueHolder),
                    new IsProxyInitialized($valueHolder),
                    new GetWrappedValueHolderValue($valueHolder),
                ],
                $skipDestructor ? [new SkipDestructor($initializer, $valueHolder)] : []
            )
        );
    }

    private function buildLazyLoadingMethodInterceptor(
        InitializerProperty $initializer,
        ValueHolderProperty $valueHolder,
        bool $fluentSafe
    ) : callable {
        return static function (ReflectionMethod $method) use ($initializer, $valueHolder, $fluentSafe) : LazyLoadingMethodInterceptor {
            $byRef = $method->returnsReference() ? '& ' : '';
            $method = LazyLoadingMethodInterceptor::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $initializer,
                $valueHolder
            );

            if ($fluentSafe) {
                $valueHolderName = '$this->' . $valueHolder->getName();
                $body = $method->getBody();
                $newBody = str_replace('return ' . $valueHolderName, 'if (' . $valueHolderName . ' === $returnValue = ' . $byRef . $valueHolderName, $body);

                if ($newBody !== $body) {
                    $method->setBody(
                        substr($newBody, 0, -1) . ') {' . "\n"
                        . '    return $this;' . "\n"
                        . '}' . "\n\n"
                        . 'return $returnValue;'
                    );
                }
            }

            return $method;
        };
    }
}
