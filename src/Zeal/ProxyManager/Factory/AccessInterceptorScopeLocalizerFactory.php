<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory;

use Closure;
use DI\Zeal\ProxyManager\Configuration;
use DI\Zeal\ProxyManager\Proxy\AccessInterceptorInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use DI\Zeal\ProxyManager\Signature\Exception\InvalidSignatureException;
use DI\Zeal\ProxyManager\Signature\Exception\MissingSignatureException;
use OutOfBoundsException;

/**
 * Factory responsible of producing proxy objects.
 */
class AccessInterceptorScopeLocalizerFactory extends AbstractBaseFactory
{
    private AccessInterceptorScopeLocalizerGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new AccessInterceptorScopeLocalizerGenerator();
    }

    /**
     * @param object $instance           the object to be localized within the access interceptor
     * @param array<string, Closure> $prefixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       before method logic is executed
     * @param array<string, Closure> $suffixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       after method logic is executed
     * @psalm-param RealObjectType $instance
     * @psalm-param array<string, Closure(
     *   RealObjectType&AccessInterceptorInterface<RealObjectType>=,
     *   RealObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   bool=
     * ) : mixed> $prefixInterceptors
     * @psalm-param array<string, Closure(
     *   RealObjectType&AccessInterceptorInterface<RealObjectType>=,
     *   RealObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   mixed=,
     *   bool=
     * ) : mixed> $suffixInterceptors
     *
     * @psalm-return RealObjectType&AccessInterceptorInterface<RealObjectType>
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(
        object $instance,
        array $prefixInterceptors = [],
        array $suffixInterceptors = []
    ) : AccessInterceptorInterface {
        $proxyClassName = $this->generateProxy($instance::class);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design).
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($instance, $prefixInterceptors, $suffixInterceptors);
    }

    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator;
    }
}