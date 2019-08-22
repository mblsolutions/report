<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastUppercaseString;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastUpperCaseStringTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastUppercaseString();

        $this->assertEquals('THIS IS THE STRING', $type->cast('this is the string'));
    }

}