<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Fixtures;

use DI\Attribute\Inject;
use DI\Attribute\Injectable;
use Exception;

/**
 * Fixture class.
 */
#[Injectable]
class Class1
{
    #[Inject]
    public Class2 $property1;

    #[Inject]
    public Interface1 $property2;

    #[Inject('namedDependency')]
    public $property3;

    #[Inject(name: 'foo')]
    public $property4;

    #[Inject]
    public LazyDependency $property5;

    public $constructorParam1;
    public $constructorParam2;
    public $constructorParam3;

    public $method1Param1;

    public $method2Param1;

    public $method3Param1;
    public $method3Param2;

    public $method4Param1;

    /**
     * @throws Exception
     */
    public function __construct(Class2 $param1, Interface1 $param2, LazyDependency $param3, $optional = true)
    {
        $this->constructorParam1 = $param1;
        $this->constructorParam2 = $param2;
        $this->constructorParam3 = $param3;

        if ($optional !== true) {
            throw new Exception('Expected optional parameter to not be defined');
        }
    }

    /**
     * Tests optional parameter is not overridden.
     */
    #[Inject]
    public function method1(Class2 $param1, $optional = true)
    {
        $this->method1Param1 = $param1;

        if ($optional !== true) {
            throw new Exception('Expected optional parameter to not be defined');
        }
    }

    /**
     * Tests automatic resolution of parameter based on the type-hinting.
     */
    #[Inject]
    public function method2(Interface1 $param1)
    {
        $this->method2Param1 = $param1;
    }

    /**
     * Tests defining parameters.
     *
     * @param string $param1
     */
    #[Inject(['namedDependency', 'foo'])]
    public function method3($param1, $param2)
    {
        $this->method3Param1 = $param1;
        $this->method3Param2 = $param2;
    }

    /**
     * Tests injecting a lazy dependency.
     */
    #[Inject]
    public function method4(LazyDependency $param1)
    {
        $this->method4Param1 = $param1;
    }
}
