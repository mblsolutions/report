<?php

namespace MBLSolutions\Report\Interfaces;

use MBLSolutions\Report\Models\Report;

interface ExportDriver
{

    /**
     * Export report result data
     *
     * @param Report $report
     * @param array $params
     * @return mixed
     */
    public function export(Report $report, array $params = []);

}