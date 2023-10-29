<?php

declare(strict_types=1);
use DI\Test\PerformanceTest\Get\A;
use DI\Test\PerformanceTest\Get\B;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\get;
use function DI\string;

return [
    'object'  => create('DI\Test\PerformanceTest\Get\GetFixture')
        ->constructor(get('array')),
    'value'   => 'foo',
    'string'  => string('Hello this is {value}'),
    'alias'   => get('factory'),
    'factory' => factory(function (ContainerInterface $c) {
        return $c->get('object');
    }),
    'array' => [
        'foo',
        'bar',
        get('string'),
    ],

    A::class  => autowire()
        ->constructorParameter('value', get('string')),
    B::class  => autowire()
        ->method('setValue', string('Wow: {string}'), get('value')),
];
