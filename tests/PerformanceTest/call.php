<?php

declare(strict_types=1);
use DI\ContainerBuilder;
use function DI\get;

require_once __DIR__ . '/vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAttributes(false);
$builder->enableCompilation(__DIR__ . '/tmp', 'Call');
$builder->addDefinitions([
    'link' => 'Hello',
]);
$container = $builder->build();

for ($i = 0; $i < 100; $i++) {
    $container->call(function ($foo, $bar) {
    }, [
        'foo',
        'bar',
    ]);
}

for ($i = 0; $i < 100; $i++) {
    $container->call(function ($foo, $bar) {
    }, [
        'foo' => 'foo',
        'bar' => 'bar',
    ]);
}

for ($i = 0; $i < 100; $i++) {
    $container->call(function (stdClass $foo) {
    });
}

for ($i = 0; $i < 100; $i++) {
    $container->call(function ($foo, $bar) {
    }, [
        'foo' => get('link'),
        'bar' => get('link'),
    ]);
}
