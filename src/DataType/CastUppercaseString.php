<?php

namespace MBLSolutions\Report\DataType;

use MBLSolutions\Report\Interfaces\ReportDataType;

class CastUppercaseString implements ReportDataType
{
    public $name = 'Uppercase String';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     */
    public function cast($value): string
    {
        return (string) strtoupper($value);
    }

}