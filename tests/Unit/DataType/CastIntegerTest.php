<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastInteger;
use MBLSolutions\Report\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class CastIntegerTest extends UnitTestCase
{

    #[Test]
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastInteger();

        $this->assertEquals(2, $type->cast('2'));
        $this->assertIsInt($type->cast('2'));
    }

    #[Test]
    public function casting_value_as_integer_converts_floats(): void
    {
        $type = new CastInteger();

        $this->assertEquals(2, $type->cast('2.99'));
    }

}