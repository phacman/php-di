<?php

declare(strict_types=1);
use DI\ContainerBuilder;
use DI\Test\PerformanceTest\Get\A;
use DI\Test\PerformanceTest\Get\B;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/get/GetFixture.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAttributes(false);
$builder->addDefinitions(__DIR__ . '/get/config.php');
$builder->enableCompilation(__DIR__ . '/tmp', 'Get');
$container = $builder->build();

$container->get(A::class);
$container->get(B::class);

$container->get('object');
$container->get('value');
$container->get('string');
$container->get('alias');
$container->get('factory');
$container->get('array');

$container->get('object');
$container->get('object');
