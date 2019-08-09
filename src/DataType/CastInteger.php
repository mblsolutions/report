<?php

namespace MBLSolutions\Report\DataType;

use MBLSolutions\Report\Interfaces\ReportDataType;

class CastInteger implements ReportDataType
{
    public $name = 'Integer';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return int
     */
    public function cast($value): int
    {
        return (int) $value;
    }

}