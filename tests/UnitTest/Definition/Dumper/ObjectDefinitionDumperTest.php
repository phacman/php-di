<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\Dumper\ObjectDefinitionDumper;
use PHPUnit\Framework\TestCase;
use function DI\create;
use function DI\get;

/**
 * @covers \DI\Definition\Dumper\ObjectDefinitionDumper
 */
class ObjectDefinitionDumperTest extends TestCase
{
    public function testAll()
    {
        $definition = create(FixtureClass::class)
            ->lazy()
            ->constructor(get('Mailer'), 'email@example.com')
            ->method('setFoo', get('SomeDependency'))
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $dumper = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = true
    __construct(
        $mailer = get(Mailer)
        $contactEmail = \'email@example.com\'
    )
    $prop = \'Some value\'
    setFoo(
        $foo = get(SomeDependency)
    )
)';

        $this->assertEquals($str, $dumper->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testClass()
    {
        $definition = create(FixtureClass::class)
            ->getDefinition('foo');
        $dumper = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
)';

        $this->assertEquals($str, $dumper->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testNonExistentClass()
    {
        $definition = create('foobar')
            ->constructor('foo', 'bar')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = #UNKNOWN# foobar
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testNonInstantiableClass()
    {
        $definition = create('ArrayAccess')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = #NOT INSTANTIABLE# ArrayAccess
    lazy = false
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testLazy()
    {
        $definition = create('stdClass')
            ->lazy()
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = stdClass
    lazy = true
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testConstructorParameters()
    {
        $definition = create(FixtureClass::class)
            ->constructor(get('Mailer'), 'email@example.com')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    __construct(
        $mailer = get(Mailer)
        $contactEmail = \'email@example.com\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testUndefinedConstructorParameter()
    {
        $definition = create(FixtureClass::class)
            ->constructor(get('Mailer'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    __construct(
        $mailer = get(Mailer)
        $contactEmail = #UNDEFINED#
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testPropertyValue()
    {
        $definition = create(FixtureClass::class)
            ->property('prop', 'Some value')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    $prop = \'Some value\'
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testPropertyGet()
    {
        $definition = create(FixtureClass::class)
            ->property('prop', get('foo'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    $prop = get(foo)
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testMethodLinkParameter()
    {
        $definition = create(FixtureClass::class)
            ->method('setFoo', get('Mailer'))
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    setFoo(
        $foo = get(Mailer)
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testMethodValueParameter()
    {
        $definition = create(FixtureClass::class)
            ->method('setFoo', 'foo')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    setFoo(
        $foo = \'foo\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }

    public function testMethodDefaultParameterValue()
    {
        $definition = create(FixtureClass::class)
            ->method('defaultValue')
            ->getDefinition('foo');
        $resolver = new ObjectDefinitionDumper();

        $str = 'Object (
    class = DI\Test\UnitTest\Definition\Dumper\FixtureClass
    lazy = false
    defaultValue(
        $foo = (default value) \'bar\'
    )
)';

        $this->assertEquals($str, $resolver->dump($definition));
        $this->assertEquals($str, (string) $definition);
    }
}

class FixtureClass
{
    public $prop;

    public function __construct($mailer, $contactEmail)
    {
    }

    public function setFoo($foo)
    {
    }

    public function defaultValue($foo = 'bar')
    {
    }
}
