<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\FileLocator;

use DI\Zeal\ProxyManager\Exception\InvalidProxyDirectoryException;
use const DIRECTORY_SEPARATOR;
use function realpath;
use function str_replace;

class FileLocator implements FileLocatorInterface
{
    protected string $proxiesDirectory;

    /**
     * @throws InvalidProxyDirectoryException
     */
    public function __construct(string $proxiesDirectory)
    {
        $absolutePath = realpath($proxiesDirectory);

        if ($absolutePath === false) {
            throw InvalidProxyDirectoryException::proxyDirectoryNotFound($proxiesDirectory);
        }

        $this->proxiesDirectory = $absolutePath;
    }

    public function getProxyFileName(string $className) : string
    {
        return $this->proxiesDirectory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }
}
