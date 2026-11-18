<?php

namespace MBLSolutions\Report\Support;

class SpreadsheetCell
{

    /**
     * Put ' before text that a spreadsheet would read as a formula
     *
     * @param mixed $value
     * @return mixed
     */
    public static function guard($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($value)) {
            return "'" . $value;
        }

        return $value;
    }

}
