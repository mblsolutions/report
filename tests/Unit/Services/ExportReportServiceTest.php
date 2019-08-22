<?php

namespace MBLSolutions\Report\Tests\Unit\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\Events\ReportDestroyed;
use MBLSolutions\Report\Events\ReportExported;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Services\ExportReportService;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ExportReportServiceTest extends LaravelTestCase
{
    /** @var Report $report */
    protected $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->report = factory(Report::class)->create();
    }

    /** @test **/
    public function can_get_the_report_driver(): void
    {
        $service = new ExportReportService($this->report, new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]));

        $this->assertInstanceOf(FakeExportDriver::class, $service->getDriver());
    }

    /** @test **/
    public function can_handle_export(): void
    {
        $service = new ExportReportService($this->report, new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]));

        /** @var Response $response */
        $response = $service->handle();

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals($response->getOriginalContent(), [
            'report' => $this->report->toArray(),
            'params' => [
                'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
            ]
        ]);
    }

    /** @test **/
    public function exporting_a_report_dispatches_event(): void
    {
        $service = new ExportReportService($this->report, new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]));

        Event::fake();

        $service->handle();

        Event::assertDispatched(ReportExported::class, static function ($event) {
            return $event->report->id === Report::first()->id;
        });
    }

}