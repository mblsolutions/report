<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Tests\UnitTestCase;

class CastTitleCaseStringTest extends UnitTestCase
{

    /** @test */
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastTitleCaseString();

        $this->assertEquals('This Is The String', $type->cast('this is the string'));
    }

}