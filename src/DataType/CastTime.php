<?php

namespace MBLSolutions\Report\DataType;

use DateTime;
use Exception;
use MBLSolutions\Report\Interfaces\ReportDataType;

class CastTime implements ReportDataType
{
    public $name = 'Time (H:i:s)';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     * @throws Exception
     */
    public function cast($value): string
    {
        return (string) (new DateTime($value))->format('H:i:s');
    }

}