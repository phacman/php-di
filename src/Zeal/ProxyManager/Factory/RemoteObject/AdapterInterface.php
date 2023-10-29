<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory\RemoteObject;

/**
 * Remote Object adapter interface.
 */
interface AdapterInterface
{
    /**
     * Call remote object.
     *
     * @param array<int, mixed> $params
     *
     * Due to BC compliance, we cannot add a native `: mixed` return type declaration here
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function call(string $wrappedClass, string $method, array $params = []);
}
