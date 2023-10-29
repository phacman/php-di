<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Destructor that skips the original destructor when the proxy is not initialized.
 */
class SkipDestructor extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty)
    {
        parent::__construct('__destruct');

        $this->setBody(
            '$this->' . $initializerProperty->getName() . ' || parent::__destruct();'
        );
    }
}
