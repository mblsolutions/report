<?php

namespace MBLSolutions\Report\DataType;

use MBLSolutions\Report\Interfaces\ReportDataType;

class CastString implements ReportDataType
{
    public $name = 'String';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     */
    public function cast($value): string
    {
        return (string) $value;
    }

}