<?php

namespace MBLSolutions\Report\Driver\QueuedExport;

use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Export\Report\ReportExport;

class TsvQueuedExport extends QueuedReportExport
{
    public string $name = 'Tab Delimited File (.tsv)';

    public function storeExportAs(string $path, string $filesystem): bool
    {
        $export = new ReportExport($this->service, $this->offset, $this->limit);

        return Excel::store($export, $path . '.tsv', $filesystem, \Maatwebsite\Excel\Excel::TSV);
    }

}