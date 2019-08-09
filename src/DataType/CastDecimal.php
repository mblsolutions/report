<?php

namespace MBLSolutions\Report\DataType;

use MBLSolutions\Report\Interfaces\ReportDataType;

class CastDecimal implements ReportDataType
{
    public $name = 'Decimal (2)';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     */
    public function cast($value): string
    {
        return (string) number_format((float) $value / 100, 2);
    }

}