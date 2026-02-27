<?php

namespace MBLSolutions\Report\Tests\Unit\Models;

use MBLSolutions\Report\Exceptions\UnknownExportDriverException;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportExportDriversTest extends LaravelTestCase
{

    #[Test]
    public function invalid_report_export_driver_throws_exception(): void
    {
        $this->expectException(UnknownExportDriverException::class);

        $driver = new ReportExportDrivers();

        $driver->findByHash('1234');
    }

}