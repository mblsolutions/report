<?php

namespace MBLSolutions\Report\Export\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use MBLSolutions\Report\Export\Report\ExportableReport;

class ReportParametersSheet extends ExportableReport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{

    public function collection()
    {
        return $this->service->getFormattedParameters($this->service->parameters)->merge([
            'name' => 'Report Run Date',
            'value' => now()->toDateTimeString()
        ]);
    }

    public function headings(): array
    {
        return [
            'Parameters',
            'Value'
        ];
    }

    public function title(): string
    {
        return 'Parameters';
    }
}