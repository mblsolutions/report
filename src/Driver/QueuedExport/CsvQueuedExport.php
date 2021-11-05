<?php

namespace MBLSolutions\Report\Driver\QueuedExport;

use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Export\Report\ReportExport;

class CsvQueuedExport extends QueuedReportExport
{
    public string $name = 'Comma Seperated File (.csv)';

    public function storeExportAs(string $path, string $filesystem): bool
    {
        $export = new ReportExport($this->service, $this->offset, $this->limit);

        return Excel::store($export, $path . '.csv', $filesystem, \Maatwebsite\Excel\Excel::CSV);
    }

}