<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Attribute\Inject;

class AnnotationFixtureScalarTypedProperty
{
    #[Inject]
    protected int $scalarTypeAndInject;
}
