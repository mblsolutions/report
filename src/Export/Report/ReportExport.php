<?php

namespace MBLSolutions\Report\Export\Report;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use MBLSolutions\Report\Support\Maps\ReportResultMap;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ReportExport extends ExportableReport implements FromCollection, WithHeadings, WithStrictNullComparison, WithCustomValueBinder
{

    public function headings(): array
    {
        return $this->service->headings()->toArray();
    }

    public function collection(): Collection
    {
        $results = $this->service->getRenderedChunk($this->offset, $this->limit);

        return $results->transform(function ($attributes) {
            return (new ReportResultMap($attributes))->format($this->service->selects());
        });
    }

    /**
     * Bind values as plain strings so already-formatted values (e.g. decimals)
     * are not reinterpreted as numbers and stripped of formatting
     *
     * @param Cell $cell
     * @param mixed $value
     * @return bool
     */
    public function bindValue(Cell $cell, $value): bool
    {
        return (new StringValueBinder())->bindValue($cell, $value);
    }

}