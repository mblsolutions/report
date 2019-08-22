<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportDataType;

class DataTypeController
{
    /** @var ReportDataType $dataType */
    protected $dataType;

    /**
     * Data Type Controller
     *
     * @param ReportDataType $dataType
     */
    public function __construct(ReportDataType $dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * Get the Report Data Types
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return $this->dataType->all();
    }

}