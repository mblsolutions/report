<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportSelectField;

class ModelController
{

    /**
     * Get a list of selectable models
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return (new ReportSelectField)->all();
    }

}