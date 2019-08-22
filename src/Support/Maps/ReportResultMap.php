<?php

namespace MBLSolutions\Report\Support\Maps;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Interfaces\ReportDataType;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\DataType\CastString;

class ReportResultMap
{

    /**
     * Format Report Result Data
     *
     * @param $attributes
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Format Attributes based on select data types
     *
     * @param Collection $selects
     * @return mixed
     */
    public function format(Collection $selects)
    {
        $selects->each(function (ReportSelect $select) {
            $this->castAttributeType($select);
        });

        return $this->attributes;
    }

    /**
     * Apply type casting on attribute
     *
     * @param ReportSelect $select
     */
    private function castAttributeType(ReportSelect $select): void
    {
        $typeFormatter = $this->getAttributeFormatter($select->type);

        $this->attributes->{$select->alias} = $typeFormatter->cast($this->attributes->{$select->alias});
    }

    /**
     * Get the select data type formatter
     *
     * @param string $namespace
     * @return ReportDataType
     */
    protected function getAttributeFormatter(string $namespace): ReportDataType
    {
        if (class_exists($namespace)) {
            $typeFormatter = new $namespace;

            if ($typeFormatter instanceof ReportDataType) {
                return $typeFormatter;
            }
        }

        return new CastString;
    }

}