<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;
use function var_export;

/**
 * Magic `__sleep` for value holder objects.
 */
class MagicSleep extends MagicMethodGenerator
{
    /**
     * Constructor.
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $valueHolderProperty)
    {
        parent::__construct($originalClass, '__sleep');

        $this->setBody('return array(' . var_export($valueHolderProperty->getName(), true) . ');');
    }
}
