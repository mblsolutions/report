<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastDate;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastDateTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastDate();

        $this->assertEquals('2019-01-01', $type->cast('01/01/2019'));
    }

    /** @test **/
    public function return_raw_value_if_value_cannot_be_cast(): void
    {
        $type = new CastDate();

        $this->assertEquals('not_a_date', $type->cast('not_a_date'));
    }

}