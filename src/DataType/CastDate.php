<?php

namespace MBLSolutions\Report\DataType;

use DateTime;
use Exception;
use MBLSolutions\Report\Interfaces\ReportDataType;

class CastDate implements ReportDataType
{
    public $name = 'Date (Y-m-d)';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     * @throws Exception
     */
    public function cast($value): string
    {
        return (string) (new DateTime($value))->format('Y-m-d');
    }

}