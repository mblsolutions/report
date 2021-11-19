<?php

namespace MBLSolutions\Report\Export\Report;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use MBLSolutions\Report\Export\Sheets\ReportDataSheet;
use MBLSolutions\Report\Export\Sheets\ReportParametersSheet;

class SheetedReportExport extends ExportableReport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new ReportDataSheet($this->service, $this->offset, $this->limit),
            new ReportParametersSheet($this->service, $this->offset, $this->limit)
        ];
    }

}