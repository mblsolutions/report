<?php

namespace MBLSolutions\Report\DataType;

use Exception;
use Illuminate\Support\Carbon;
use MBLSolutions\Report\Interfaces\ReportDataType;

class CastDate implements ReportDataType
{
    public $name = 'Date (Y-m-d)';

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return string
     */
    public function cast($value): string
    {
        try {
            return (string) (new Carbon($value))->format('Y-m-d');
        } catch (Exception $exception) {
            return $value;
        }
    }

}