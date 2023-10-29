<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator;

use DI\Zeal\ProxyManager\Factory\RemoteObject\AdapterInterface;
use DI\Zeal\ProxyManager\Generator\Util\IdentifierSuffixer;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Property that contains the remote object adapter.
 */
class AdapterProperty extends PropertyGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('adapter'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\' . AdapterInterface::class . ' Remote web service adapter');
    }
}
