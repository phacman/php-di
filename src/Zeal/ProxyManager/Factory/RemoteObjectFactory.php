<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory;

use DI\Zeal\ProxyManager\Configuration;
use DI\Zeal\ProxyManager\Factory\RemoteObject\AdapterInterface;
use DI\Zeal\ProxyManager\Proxy\RemoteObjectInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use DI\Zeal\ProxyManager\ProxyGenerator\RemoteObjectGenerator;
use DI\Zeal\ProxyManager\Signature\Exception\InvalidSignatureException;
use DI\Zeal\ProxyManager\Signature\Exception\MissingSignatureException;
use function is_object;
use OutOfBoundsException;

/**
 * Factory responsible of producing remote proxy objects.
 */
class RemoteObjectFactory extends AbstractBaseFactory
{
    protected AdapterInterface $adapter;
    private RemoteObjectGenerator $generator;

    public function __construct(AdapterInterface $adapter, ?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->adapter = $adapter;
        $this->generator = new RemoteObjectGenerator();
    }

    /**
     * @psalm-param RealObjectType|class-string<RealObjectType> $instanceOrClassName
     *
     * @psalm-return RealObjectType&RemoteObjectInterface
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy($instanceOrClassName) : RemoteObjectInterface
    {
        $proxyClassName = $this->generateProxy(
            is_object($instanceOrClassName) ? $instanceOrClassName::class : $instanceOrClassName
        );

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design).
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($this->adapter);
    }

    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator ?? $this->generator = new RemoteObjectGenerator();
    }
}
