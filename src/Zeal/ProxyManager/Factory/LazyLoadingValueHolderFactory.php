<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory;

use Closure;
use DI\Zeal\ProxyManager\Configuration;
use DI\Zeal\ProxyManager\Proxy\ValueHolderInterface;
use DI\Zeal\ProxyManager\Proxy\VirtualProxyInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Factory responsible of producing virtual proxy instances.
 */
class LazyLoadingValueHolderFactory extends AbstractBaseFactory
{
    private LazyLoadingValueHolderGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new LazyLoadingValueHolderGenerator();
    }

    /**
     * @param array<string, mixed> $proxyOptions
     * @psalm-param class-string<RealObjectType> $className
     * @psalm-param Closure(
     *   RealObjectType|null=,
     *   RealObjectType&ValueHolderInterface<RealObjectType>&VirtualProxyInterface=,
     *   string=,
     *   array<string, mixed>=,
     *   ?Closure=
     * ) : bool $initializer
     * @psalm-param array{skipDestructor?: bool, fluentSafe?: bool} $proxyOptions
     *
     * @psalm-return RealObjectType&ValueHolderInterface<RealObjectType>&VirtualProxyInterface
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(
        string $className,
        Closure $initializer,
        array $proxyOptions = []
    ) : VirtualProxyInterface {
        $proxyClassName = $this->generateProxy($className, $proxyOptions);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design).
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($initializer);
    }

    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
