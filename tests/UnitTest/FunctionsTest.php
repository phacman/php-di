<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Reference;
use DI\Definition\ArrayDefinition;
use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\DecoratorDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Definition\Helper\FactoryDefinitionHelper;
use DI\Definition\ObjectDefinition;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;
use function DI\add;
use function DI\autowire;
use function DI\create;
use function DI\decorate;
use function DI\env;
use function DI\factory;
use function DI\get;
use function DI\string;
use function DI\value;

/**
 * Tests the helper functions.
 */
class FunctionsTest extends TestCase
{
    /**
     * @covers ::\DI\value
     */
    public function test_value()
    {
        $definition = value('foo');

        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getValue());
    }

    /**
     * @covers ::\DI\create
     */
    public function test_create()
    {
        $helper = create();

        $this->assertInstanceOf(CreateDefinitionHelper::class, $helper);
        $definition = $helper->getDefinition('entry');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('entry', $definition->getClassName());

        $helper = create('foo');

        $this->assertInstanceOf(CreateDefinitionHelper::class, $helper);
        $definition = $helper->getDefinition('entry');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getClassName());
    }

    /**
     * @covers ::\DI\autowire
     */
    public function test_autowire()
    {
        $helper = autowire();

        $this->assertInstanceOf(AutowireDefinitionHelper::class, $helper);
        $definition = $helper->getDefinition('entry');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('entry', $definition->getClassName());

        $helper = autowire('foo');

        $this->assertInstanceOf(AutowireDefinitionHelper::class, $helper);
        $definition = $helper->getDefinition('entry');
        $this->assertInstanceOf(ObjectDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getClassName());
    }

    /**
     * @covers ::\DI\factory
     */
    public function test_factory()
    {
        $helper = factory(function () {
            return 42;
        });

        $this->assertInstanceOf(FactoryDefinitionHelper::class, $helper);
        $definition = $helper->getDefinition('entry');
        $this->assertInstanceOf(FactoryDefinition::class, $definition);
        $callable = $definition->getCallable();
        $this->assertEquals(42, $callable());
    }

    /**
     * @covers ::\DI\decorate
     */
    public function test_decorate()
    {
        $helper = decorate(function () {
            return 42;
        });

        $this->assertInstanceOf(FactoryDefinitionHelper::class, $helper);
        $definition = $helper->getDefinition('entry');
        $this->assertInstanceOf(DecoratorDefinition::class, $definition);
        $callable = $definition->getCallable();
        $this->assertEquals(42, $callable());
    }

    /**
     * @covers ::\DI\get
     */
    public function test_get()
    {
        $reference = get('foo');

        $this->assertInstanceOf(Reference::class, $reference);
        $this->assertEquals('foo', $reference->getTargetEntryName());
    }

    /**
     * @covers ::\DI\env
     */
    public function test_env()
    {
        $definition = env('foo');

        $this->assertInstanceOf(EnvironmentVariableDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getVariableName());
        $this->assertFalse($definition->isOptional());
    }

    /**
     * @covers ::\DI\env
     */
    public function test_env_default_value()
    {
        $definition = env('foo', 'default');

        $this->assertInstanceOf(EnvironmentVariableDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getVariableName());
        $this->assertTrue($definition->isOptional());
        $this->assertEquals('default', $definition->getDefaultValue());
    }

    /**
     * @covers ::\DI\env
     */
    public function test_env_default_value_null()
    {
        $definition = env('foo', null);

        $this->assertInstanceOf(EnvironmentVariableDefinition::class, $definition);
        $this->assertEquals('foo', $definition->getVariableName());
        $this->assertTrue($definition->isOptional());
        $this->assertNull($definition->getDefaultValue());
    }

    /**
     * @covers ::\DI\add
     */
    public function test_add_value()
    {
        $definition = add('hello');
        $definition->setName('foo');

        $this->assertInstanceOf(ArrayDefinitionExtension::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $definition->setExtendedDefinition(new ArrayDefinition([]));
        $this->assertEquals(['hello'], $definition->getValues());
    }

    /**
     * @covers ::\DI\add
     */
    public function test_add_array()
    {
        $definition = add(['hello', 'world']);
        $definition->setName('foo');

        $this->assertInstanceOf(ArrayDefinitionExtension::class, $definition);
        $this->assertEquals('foo', $definition->getName());
        $definition->setExtendedDefinition(new ArrayDefinition([]));
        $this->assertEquals(['hello', 'world'], $definition->getValues());
    }

    /**
     * @covers ::\DI\string
     */
    public function test_string()
    {
        $definition = string('bar');

        $this->assertInstanceOf(StringDefinition::class, $definition);
        $this->assertEquals('bar', $definition->getExpression());
    }
}
