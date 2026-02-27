<?php

namespace MBLSolutions\Report\Tests\Unit\DataType;

use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class CastTitleCaseStringTest extends UnitTestCase
{

    #[Test]
    public function can_cast_value_as_data_type(): void
    {
        $type = new CastTitleCaseString();

        $this->assertEquals('This Is The String', $type->cast('this is the string'));
    }

}