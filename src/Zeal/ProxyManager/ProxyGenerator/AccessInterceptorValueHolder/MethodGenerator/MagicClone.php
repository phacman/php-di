<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use function array_keys;
use DI\Zeal\ProxyManager\Generator\MagicMethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;
use function str_replace;

/**
 * Magic `__clone` for lazy loading value holder objects.
 */
class MagicClone extends MagicMethodGenerator
{
    private const TEMPLATE = <<<'PHP'
        $this->{{$valueHolder}} = clone $this->{{$valueHolder}};

        foreach ($this->{{$prefix}} as $key => $value) {
            $this->{{$prefix}}[$key] = clone $value;
        }

        foreach ($this->{{$suffix}} as $key => $value) {
            $this->{{$suffix}}[$key] = clone $value;
        }
        PHP;

    /**
     * Constructor.
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolderProperty,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct($originalClass, '__clone');

        $valueHolder = $valueHolderProperty->getName();
        $prefix = $prefixInterceptors->getName();
        $suffix = $suffixInterceptors->getName();

        $replacements = [
            '{{$valueHolder}}' => $valueHolder,
            '{{$prefix}}' => $prefix,
            '{{$suffix}}' => $suffix,
        ];

        $this->setBody(str_replace(
            array_keys($replacements),
            $replacements,
            self::TEMPLATE
        ));
    }
}
