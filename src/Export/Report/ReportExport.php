<?php

namespace MBLSolutions\Report\Export\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use MBLSolutions\Report\Services\BuildReportService;

class ReportExport implements FromCollection, WithHeadings
{
    protected BuildReportService $service;

    protected int $offset;

    protected int $limit;


    public function __construct(BuildReportService $service, int $offset, int $limit)
    {
        $this->service = $service;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function headings(): array
    {
        return $this->service->headings()->toArray();
    }

    public function collection(): Collection
    {
        return $this->service->getRenderedChunk($this->offset, $this->limit);
    }

}