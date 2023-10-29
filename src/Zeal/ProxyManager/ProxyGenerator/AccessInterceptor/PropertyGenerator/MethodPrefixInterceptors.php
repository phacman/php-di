<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use DI\Zeal\ProxyManager\Generator\Util\IdentifierSuffixer;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Property that contains the interceptor for operations to be executed before method execution.
 */
class MethodPrefixInterceptors extends PropertyGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('methodPrefixInterceptors'));

        $this->setDefaultValue([]);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\Closure[] map of interceptors to be called per-method before execution');
    }
}
