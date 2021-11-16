<?php

namespace MBLSolutions\Report\Http\Controllers;

use Illuminate\Support\Collection;
use MBLSolutions\Report\Models\ReportConnection;
use MBLSolutions\Report\Models\ReportMiddlewareOptions;
use MBLSolutions\Report\Models\ReportSelectField;
use MBLSolutions\Report\Models\ReportDataType;

class ManageSettingsController
{

    /**
     * Get Manage Settings
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return new Collection([
            'connections' => (new ReportConnection)->all(),
            'middleware' => (new ReportMiddlewareOptions())->all(),
            'models' => (new ReportSelectField)->all(),
            'data_types' => (new ReportDataType)->all()
        ]);
    }

}