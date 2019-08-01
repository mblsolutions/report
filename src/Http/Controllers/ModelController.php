<?php

namespace MBLSolutions\Report\Http\Controllers;

class ModelController
{

    /**
     * Get a list of selectable models
     *
     * @return array
     */
    public function index(): array
    {
        return config('report.models');
    }

}