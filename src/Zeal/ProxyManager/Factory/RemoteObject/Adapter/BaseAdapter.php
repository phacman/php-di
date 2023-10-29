<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\Factory\RemoteObject\Adapter;

use function array_key_exists;
use DI\Zeal\ProxyManager\Factory\RemoteObject\AdapterInterface;
use Laminas\Server\Client;

/**
 * Remote Object base adapter.
 */
abstract class BaseAdapter implements AdapterInterface
{
    protected $client;

    /**
     * Service name mapping.
     *
     * @var array<string, string>
     */
    protected $map = [];

    /**
     * Constructor.
     *
     * @param array<string, string> $map map of service names to their aliases
     */
    public function __construct(Client $client, array $map = [])
    {
        $this->client = $client;
        $this->map = $map;
    }

    public function call(string $wrappedClass, string $method, array $params = [])
    {
        $serviceName = $this->getServiceName($wrappedClass, $method);

        if (array_key_exists($serviceName, $this->map)) {
            $serviceName = $this->map[$serviceName];
        }

        return $this->client->call($serviceName, $params);
    }

    /**
     * Get the service name will be used by the adapter.
     */
    abstract protected function getServiceName(string $wrappedClass, string $method) : string;
}
