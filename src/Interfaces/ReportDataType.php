<?php

namespace MBLSolutions\Report\Interfaces;

interface ReportDataType
{

    /**
     * Cast value as data type
     *
     * @param mixed $value
     * @return mixed
     */
    public function cast($value);

}