<?php

namespace MBLSolutions\Report\Export\Report;

use MBLSolutions\Report\Services\BuildReportService;

abstract class ExportableReport
{
    protected BuildReportService $service;

    protected int $offset;

    protected int $limit;

    /**
     * @param BuildReportService $service
     * @param int $offset
     * @param int $limit
     */
    public function __construct(BuildReportService $service, int $offset, int $limit)
    {
        $this->service = $service;
        $this->offset = $offset;
        $this->limit = $limit;
    }

}