<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory;

use DI\Zeal\ProxyManager\Configuration;
use DI\Zeal\ProxyManager\Proxy\NullObjectInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\NullObjectGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use DI\Zeal\ProxyManager\Signature\Exception\InvalidSignatureException;
use DI\Zeal\ProxyManager\Signature\Exception\MissingSignatureException;
use function is_object;
use OutOfBoundsException;

/**
 * Factory responsible of producing proxy objects.
 */
class NullObjectFactory extends AbstractBaseFactory
{
    private NullObjectGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new NullObjectGenerator();
    }

    /**
     * @param object|string $instanceOrClassName the object to be wrapped or interface to transform to null object
     * @psalm-param RealObjectType|class-string<RealObjectType> $instanceOrClassName
     *
     * @psalm-return RealObjectType&NullObjectInterface
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(object|string $instanceOrClassName) : NullObjectInterface
    {
        $className = is_object($instanceOrClassName) ? $instanceOrClassName::class : $instanceOrClassName;
        $proxyClassName = $this->generateProxy($className);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design).
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor();
    }

    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
