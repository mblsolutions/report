<?php

namespace MBLSolutions\Report\Export\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use MBLSolutions\Report\Services\BuildReportService;

class ReportExport implements FromCollection
{

    protected BuildReportService $service;

    public function __construct(BuildReportService $service)
    {
        $this->service = $service;
    }

    public function collection(): Collection
    {
        return $this->service->render()->get('data');
    }

}