<?php

namespace MBLSolutions\Report\DataType;

use Exception;
use Illuminate\Support\Carbon;
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
        try {
            return (string) (new Carbon($value))->format('H:i:s');
        } catch (Exception $exception) {
            return $value;
        }
    }

}