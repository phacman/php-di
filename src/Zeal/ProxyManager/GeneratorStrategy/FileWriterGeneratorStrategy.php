<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\GeneratorStrategy;

use DI\Zeal\ProxyManager\Exception\FileNotWritableException;
use DI\Zeal\ProxyManager\FileLocator\FileLocatorInterface;
use Laminas\Code\Generator\ClassGenerator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generator strategy that writes the generated classes to disk while generating them.
 *
 * {@inheritDoc}
 */
class FileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    protected FileLocatorInterface $fileLocator;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * Write generated code to disk and return the class code.
     *
     * {@inheritDoc}
     *
     * @throws FileNotWritableException
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        $generatedCode = $classGenerator->generate();
        $className = $classGenerator->getNamespaceName() . '\\' . $classGenerator->getName();
        $fileName = $this->fileLocator->getProxyFileName($className);

        try {
            (new Filesystem())->dumpFile($fileName, "<?php\n\n" . $generatedCode);

            return $generatedCode;
        } catch (IOException $e) {
            throw FileNotWritableException::fromPrevious($e);
        }
    }
}
