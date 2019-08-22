<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastString;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastStringTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastString();

        $this->assertEquals('2', $type->cast(2));
        $this->assertIsString($type->cast('2'));
    }

}