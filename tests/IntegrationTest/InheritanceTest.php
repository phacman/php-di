<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\ContainerBuilder;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\BaseClass;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\Dependency;
use DI\Test\IntegrationTest\Fixtures\InheritanceTest\SubClass;
use function DI\create;
use function DI\get;

/**
 * Test class for bean injection.
 */
class InheritanceTest extends BaseContainerTest
{
    /**
     * Test a dependency is injected if the injection is defined on a parent class.
     *
     * @dataProvider provideContainer
     */
    public function test_dependency_is_injected_if_injection_defined_on_parent_class_with_config(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->useAttributes(false);
        $builder->addDefinitions([
            Dependency::class => create(),
            BaseClass::class => create(SubClass::class)
                ->property('property1', get(Dependency::class))
                ->property('property4', get(Dependency::class))
                ->constructor(get(Dependency::class))
                ->method('setProperty2', get(Dependency::class)),
            SubClass::class => create()
                ->property('property1', get(Dependency::class))
                ->property('property4', get(Dependency::class))
                ->constructor(get(Dependency::class))
                ->method('setProperty2', get(Dependency::class)),
        ]);

        /** @var $instance SubClass */
        $instance = $builder->build()->get(SubClass::class);

        $this->assertInstanceOf(Dependency::class, $instance->property1);
        $this->assertInstanceOf(Dependency::class, $instance->property2);
        $this->assertInstanceOf(Dependency::class, $instance->property3);
        $this->assertInstanceOf(Dependency::class, $instance->property4);
    }

    /**
     * Test a dependency is injected if the injection is defined on a parent class.
     *
     * @dataProvider provideContainer
     */
    public function test_dependency_is_injected_if_injection_defined_on_parent_class_with_attributes(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->useAttributes(true);
        $builder->addDefinitions([
            BaseClass::class => get(SubClass::class),
        ]);

        /** @var $instance SubClass */
        $instance = $builder->build()->get(SubClass::class);

        $this->assertInstanceOf(Dependency::class, $instance->property1);
        $this->assertInstanceOf(Dependency::class, $instance->property2);
        $this->assertInstanceOf(Dependency::class, $instance->property3);
        $this->assertInstanceOf(Dependency::class, $instance->property4);
    }

    /**
     * Test a dependency is injected if the injection is defined on a child class.
     *
     * @dataProvider provideContainer
     */
    public function test_dependency_is_injected_if_injection_defined_on_base_class_with_config(ContainerBuilder $builder)
    {
        $builder->useAutowiring(false);
        $builder->useAttributes(false);
        $builder->addDefinitions([
            Dependency::class => create(),
            BaseClass::class => create(SubClass::class)
                ->property('property1', get(Dependency::class))
                ->property('property4', get(Dependency::class))
                ->constructor(get(Dependency::class))
                ->method('setProperty2', get(Dependency::class)),
            SubClass::class => create()
                ->property('property1', get(Dependency::class))
                ->property('property4', get(Dependency::class))
                ->constructor(get(Dependency::class))
                ->method('setProperty2', get(Dependency::class)),
        ]);

        /** @var $instance SubClass */
        $instance = $builder->build()->get(BaseClass::class);

        $this->assertInstanceOf(Dependency::class, $instance->property1);
        $this->assertInstanceOf(Dependency::class, $instance->property2);
        $this->assertInstanceOf(Dependency::class, $instance->property3);
        $this->assertInstanceOf(Dependency::class, $instance->property4);
    }

    /**
     * Test a dependency is injected if the injection is defined on a child class.
     *
     * @dataProvider provideContainer
     */
    public function test_dependency_is_injected_if_injection_defined_on_base_class_with_attributes(ContainerBuilder $builder)
    {
        $builder->useAutowiring(true);
        $builder->useAttributes(true);
        $builder->addDefinitions([
            BaseClass::class => get(SubClass::class),
        ]);

        /** @var $instance SubClass */
        $instance = $builder->build()->get(BaseClass::class);

        $this->assertInstanceOf(Dependency::class, $instance->property1);
        $this->assertInstanceOf(Dependency::class, $instance->property2);
        $this->assertInstanceOf(Dependency::class, $instance->property3);
        $this->assertInstanceOf(Dependency::class, $instance->property4);
    }
}
