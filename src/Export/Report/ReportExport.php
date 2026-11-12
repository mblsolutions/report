<?php

namespace MBLSolutions\Report\Export\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use MBLSolutions\Report\Support\SpreadsheetCell;

class ReportExport extends ExportableReport implements FromCollection, WithHeadings, WithStrictNullComparison
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

}