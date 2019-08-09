<?php

namespace MBLSolutions\Report\DataType;

use MBLSolutions\Report\Interfaces\ReportDataType;

class CastTitleCaseString implements ReportDataType
{
    public $name = 'Title Case String';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     */
    public function cast($value): string
    {
        return (string) ucwords($value);
    }

}