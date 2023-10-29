<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use DI\Zeal\ProxyManager\Generator\MethodGenerator;
use DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;
use function implode;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;

/**
 * Method with additional pre- and post- interceptor logic in the body.
 */
class InterceptedMethod extends MethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $valueHolderProperty,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) : self {
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $forwardedParams = [];

        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[] = ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $method->setBody(InterceptorGenerator::createInterceptedMethodBody(
            '$returnValue = $this->' . $valueHolderProperty->getName() . '->'
            . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
            $method,
            $valueHolderProperty,
            $prefixInterceptors,
            $suffixInterceptors,
            $originalMethod
        ));

        return $method;
    }
}
