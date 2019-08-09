<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportDataType;

class DataTypeController
{

    /**
     * Get the Report Data Types
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return (new ReportDataType)->all();
    }

}