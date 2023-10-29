<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use function DI\factory;

/**
 * @see https://github.com/mnapoli/PHP-DI/issues/70
 * @see https://github.com/mnapoli/PHP-DI/issues/76
 */
class Issue70and76Test extends BaseContainerTest
{
    /**
     * @test
     * @dataProvider provideContainer
     */
    public function valueDefinitionShouldOverrideReflectionDefinition(ContainerBuilder $builder)
    {
        $container = $builder->build();

        $container->set('stdClass', 'foo');
        $this->assertEquals('foo', $container->get('stdClass'));
    }

    /**
     * @test
     * @dataProvider provideContainer
     */
    public function closureDefinitionShouldOverrideReflectionDefinition(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'stdClass' => factory(function () {
                return 'foo';
            }),
        ]);

        $this->assertEquals('foo', $builder->build()->get('stdClass'));
    }
}
