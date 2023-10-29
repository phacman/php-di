<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Fixtures\InheritanceTest;

use DI\Attribute\Inject;

/**
 * Fixture class.
 */
class SubClass extends BaseClass
{
    #[Inject]
    public Dependency $property4;
}
