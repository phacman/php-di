<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator;

use function array_map;
use function array_merge;
use DI\Zeal\ProxyManager\Exception\InvalidProxiedClassException;
use DI\Zeal\ProxyManager\Generator\MethodGenerator as ProxyManagerMethodGenerator;
use DI\Zeal\ProxyManager\Generator\Util\ClassGeneratorUtils;
use DI\Zeal\ProxyManager\Proxy\GhostObjectInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicIsset;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SkipDestructor;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\Properties;
use DI\Zeal\ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionClass;
use ReflectionMethod;

/**
 * Generator for proxies implementing {@see GhostObjectInterface}.
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * @psalm-param array{skippedProperties?: array<int, string>, skipDestructor?: bool} $proxyOptions
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = [])
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $filteredProperties = Properties::fromReflectionClass($originalClass)
            ->filter($proxyOptions['skippedProperties'] ?? []);

        $publicProperties = new PublicPropertiesMap($filteredProperties, true);
        $privateProperties = new PrivatePropertiesMap($filteredProperties);
        $protectedProperties = new ProtectedPropertiesMap($filteredProperties);
        $skipDestructor = ($proxyOptions['skipDestructor'] ?? false) && $originalClass->hasMethod('__destruct');

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([GhostObjectInterface::class]);
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($privateProperties);
        $classGenerator->addPropertyFromGenerator($protectedProperties);

        $init = new CallInitializer($initializer, $initializationTracker, $filteredProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) : void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                $this->getAbstractProxiedMethods($originalClass, $skipDestructor),
                [
                    $init,
                    new StaticProxyConstructor($initializer, $filteredProperties),
                    new MagicGet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties,
                        $initializationTracker
                    ),
                    new MagicSet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicClone($originalClass, $initializer, $init),
                    new MagicSleep($originalClass, $initializer, $init),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $init),
                    new IsProxyInitialized($initializer),
                ],
                $skipDestructor ? [new SkipDestructor($initializer)] : []
            )
        );
    }

    /**
     * Retrieves all abstract methods to be proxied.
     *
     * @return MethodGenerator[]
     */
    private function getAbstractProxiedMethods(ReflectionClass $originalClass, bool $skipDestructor) : array
    {
        $excludedMethods = ProxiedMethodsFilter::DEFAULT_EXCLUDED;

        if ($skipDestructor) {
            $excludedMethods[] = '__destruct';
        }

        return array_map(
            static function (ReflectionMethod $method) : ProxyManagerMethodGenerator {
                $generated = ProxyManagerMethodGenerator::fromReflectionWithoutBodyAndDocBlock(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                );

                $generated->setAbstract(false);

                return $generated;
            },
            ProxiedMethodsFilter::getAbstractProxiedMethods($originalClass, $excludedMethods)
        );
    }
}
