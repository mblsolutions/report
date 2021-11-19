<?php

namespace MBLSolutions\Report\Driver\QueuedExport;

use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Export\Report\SheetedReportExport;

class XlsxQueuedExport extends QueuedReportExport
{
    public string $name = '2007+ Excel Spreadsheet File (.xlsx)';

    public function storeExportAs(string $path, string $filesystem): bool
    {
        $export = new SheetedReportExport($this->service, $this->offset, $this->limit);

        return Excel::store($export, $path . '.xlsx', $filesystem, \Maatwebsite\Excel\Excel::XLSX);
    }

}