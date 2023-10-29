<?php
declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\AutowireDefinition;

use stdClass;

class Php71
{
    /**
     * @var null|stdClass
     */
    public $param;

    public function __construct(?stdClass $param)
    {
        $this->param = $param;
    }
}
