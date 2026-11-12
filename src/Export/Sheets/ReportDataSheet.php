<?php

namespace MBLSolutions\Report\Export\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use MBLSolutions\Report\Support\SpreadsheetCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use MBLSolutions\Report\Export\Report\ExportableReport;

class ReportDataSheet extends ExportableReport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{

    public function headings(): array
    {
        return $this->service->headings()->toArray();
    }

    public function collection(): Collection
    {
        return $this->service->getRenderedChunk($this->offset, $this->limit)
            ->map(static fn ($row) => array_map([SpreadsheetCell::class, 'guard'], (array) $row));
    }

    public function title(): string
    {
        return $this->service->report->getAttribute('name');
    }
}