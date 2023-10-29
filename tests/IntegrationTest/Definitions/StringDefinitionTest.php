<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\BaseContainerTest;
use DI\DependencyException;
use function DI\string;

/**
 * Test string definitions.
 */
class StringDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_string_without_placeholder(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo' => string('bar'),
        ]);
        $container = $builder->build();

        self::assertEntryIsCompiled($container, 'foo');
        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_string_with_placeholder(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo'         => 'bar',
            'test-string' => string('Hello {foo}'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Hello bar', $container->get('test-string'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_string_with_multiple_placeholders(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'foo'         => 'bar',
            'bim'         => 'bam',
            'test-string' => string('Hello {foo}, {bim}'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Hello bar, bam', $container->get('test-string'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_nested_string_expressions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'name'        => 'John',
            'welcome'     => string('Welcome {name}'),
            'test-string' => string('{welcome}!'),
        ]);
        $container = $builder->build();

        $this->assertEquals('Welcome John!', $container->get('test-string'));
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_string_with_nonexistent_placeholder(ContainerBuilder $builder)
    {
        $this->expectException(DependencyException::class);
        $this->expectExceptionMessage('Error while parsing string expression for entry \'test-string\': No entry or class found for \'foo\'');
        $builder->addDefinitions([
            'test-string' => string('Hello {foo}'),
        ]);
        $container = $builder->build();

        $container->get('test-string');
    }
}
