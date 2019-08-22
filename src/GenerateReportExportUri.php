<?php

namespace MBLSolutions\Report;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class GenerateReportExportUri
{

    /**
     * Generate a Report Export URI
     *
     * @param array $params
     * @param Carbon|null $carbon
     * @return string
     */
    public function __invoke(array $params, Carbon $carbon = null): string
    {
        return URL::temporarySignedRoute('report.export', $carbon ?? Carbon::now()->addHour(), $params);
    }

}