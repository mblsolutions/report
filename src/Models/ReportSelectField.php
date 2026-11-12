<?php

namespace MBLSolutions\Report\Models;

use MBLSolutions\Report\Support\ConfigModel;

class ReportSelectField extends ConfigModel
{
    /** {@inheritDoc} */
    protected string $key = 'report.models';

    /**
     * Columns a select field may use for its value and name
     *
     * @return array
     */
    public static function columns(): array
    {
        return config('report.select_columns', ['id', 'uuid', 'slug', 'sku', 'urn', 'name']);
    }

}