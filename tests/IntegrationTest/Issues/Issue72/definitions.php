<?php

declare(strict_types=1);

use function DI\create;
use function DI\factory;
use function DI\get;

return [
    'service2' => factory(function () {
        $value = new stdClass();
        $value->foo = 'bar';

        return $value;
    }),
    'DI\Test\IntegrationTest\Issues\Issue72\Class1' => create()
            ->constructor(get('service2')),
];
