<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues\Issue72;

use DI\Attribute\Inject;

class Class1
{
    public $arg1;

    #[Inject(['service1'])]
    public function __construct(\stdClass $arg1)
    {
        $this->arg1 = $arg1;
    }
}
