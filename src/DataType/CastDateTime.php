<?php

namespace MBLSolutions\Report\DataType;

use DateTime;
use Exception;
use MBLSolutions\Report\Interfaces\ReportDataType;

class CastDateTime implements ReportDataType
{
    public $name = 'Date Time (Y-m-d H:i:s)';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     * @throws Exception
     */
    public function cast($value): string
    {
        return (string) (new DateTime($value))->format('Y-m-d H:i:s');
    }

}