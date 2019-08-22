<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastDecimal;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastDecimalTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastDecimal();

        $this->assertEquals('2.99', $type->cast(299));
    }

}