<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory\RemoteObject\Adapter;

/**
 * Remote Object JSON RPC adapter.
 */
class JsonRpc extends BaseAdapter
{
    protected function getServiceName(string $wrappedClass, string $method) : string
    {
        return $wrappedClass . '.' . $method;
    }
}
