<?php

namespace MBLSolutions\Report\Http\Controllers;

use MBLSolutions\Report\Http\Resources\ReportResource;
use MBLSolutions\Report\Models\Report;

class ManageReportController
{

    public function index(): array
    {
        // TODO implement method

        return [];
    }

    public function store(): array
    {
        // TODO implement method

        return [];
    }

    /**
     * Show a Report
     *
     * @param int|null $id
     * @return ReportResource
     */
    public function show($id = null): ReportResource
    {
        if ($id === null) {
            $report = Report::findOrFail($id);
        } else {
            $report = new Report();
        }

        return new ReportResource($report);
    }

    public function update(): array
    {
        // TODO implement method

        return [];
    }

    public function destroy(): array
    {
        // TODO implement method

        return [];
    }



}