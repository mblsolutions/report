<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastInteger;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastIntegerTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastInteger();

        $this->assertEquals(2, $type->cast('2'));
        $this->assertIsInt($type->cast('2'));
    }

    /** @test **/
    public function casting_value_as_integer_converts_floats(): void
    {
        $type = new CastInteger();

        $this->assertEquals(2, $type->cast('2.99'));
    }

}