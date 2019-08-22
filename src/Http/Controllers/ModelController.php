<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportSelectField;

class ModelController
{
    /** @var ReportSelectField $model */
    protected $model;

    /**
     * ModelController constructor.
     *
     * @param ReportSelectField $field
     */
    public function __construct(ReportSelectField $field)
    {
        $this->model = $field;
    }

    /**
     * Get a list of selectable models
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return $this->model->all();
    }

}