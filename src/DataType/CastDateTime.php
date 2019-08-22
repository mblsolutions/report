<?php

namespace MBLSolutions\Report\DataType;

use Exception;
use Illuminate\Support\Carbon;
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
        try {
            return (string) (new Carbon($value))->format('Y-m-d H:i:s');
        } catch (Exception $exception) {
            return $value;
        }
    }

}