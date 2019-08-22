<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastDateTime;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastDateTimeTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastDateTime();

        $this->assertEquals('2019-01-01 00:00:00', $type->cast('01/01/2019'));
    }

    /** @test **/
    public function return_raw_value_if_value_cannot_be_cast(): void
    {
        $type = new CastDateTime();

        $this->assertEquals('not_a_date', $type->cast('not_a_date'));
    }

}