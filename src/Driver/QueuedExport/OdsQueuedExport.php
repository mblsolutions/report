<?php

namespace MBLSolutions\Report\Driver\QueuedExport;

use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Export\Report\SheetedReportExport;

class OdsQueuedExport extends QueuedReportExport
{
    public string $name = 'OpenOffice Spreadsheet File (.ods)';

    public function storeExportAs(string $path, string $filesystem): bool
    {
        $export = new SheetedReportExport($this->service, $this->offset, $this->limit);

        return Excel::store($export, $path . '.ods', $filesystem, \Maatwebsite\Excel\Excel::ODS);
    }

}