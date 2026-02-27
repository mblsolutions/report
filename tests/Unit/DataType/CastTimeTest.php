<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastTime;
use MBLSolutions\Report\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class CastTimeTest extends UnitTestCase
{

    #[Test]
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastTime();

        $this->assertEquals('12:59:00', $type->cast('12:59'));
    }

    #[Test]
    public function return_raw_value_if_value_cannot_be_cast(): void
    {
        $type = new CastTime();

        $this->assertEquals('not_a_time', $type->cast('not_a_time'));
    }

}