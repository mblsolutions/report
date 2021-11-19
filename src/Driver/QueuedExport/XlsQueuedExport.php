<?php

namespace MBLSolutions\Report\Driver\QueuedExport;

use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Export\Report\SheetedReportExport;

class XlsQueuedExport extends QueuedReportExport
{
    public string $name = 'Excel Spreadsheet (.xls)';

    public function storeExportAs(string $path, string $filesystem): bool
    {
        $export = new SheetedReportExport($this->service, $this->offset, $this->limit);

        return Excel::store($export, $path . '.xls', $filesystem, \Maatwebsite\Excel\Excel::XLS);
    }

}