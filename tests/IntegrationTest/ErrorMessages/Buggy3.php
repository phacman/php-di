<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\Attribute\Inject;

class Buggy3
{
    #[Inject(name: 'namedDependency')]
    public $dependency;
}
