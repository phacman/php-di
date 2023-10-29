<?php

declare(strict_types=1);

namespace DI\Zeal\ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use DI\Zeal\ProxyManager\Generator\Util\IdentifierSuffixer;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;

/**
 * Property that contains the initializer for a lazy object.
 */
class InitializationTracker extends PropertyGenerator
{
    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('initializationTracker'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var bool tracks initialization status - true while the object is initializing');
        $this->setDefaultValue(false);
    }
}
