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
        $parameters = array_merge($this->service->parameters->toArray(), [
            'Report Run Date' => now()->toDateTimeString()
        ]);

        return $this->service->getFormattedParameters($parameters);
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