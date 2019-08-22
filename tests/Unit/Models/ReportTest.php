<?php

namespace MBLSolutions\Report\Tests\Unit\Models;

use Illuminate\Http\Request;
use MBLSolutions\Report\GenerateReportExportUri;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ReportTest extends LaravelTestCase
{

    /** @test **/
    public function can_get_export_link(): void
    {
        $this->actingAs(new User);

        $report = factory(Report::class)->create();

        $this->app->instance(GenerateReportExportUri::class, static function () {
            return 'http://localhost/report/1/export?expires=60&signature=1234abcd';
        });

        $this->assertEquals(
            $report->getExportLink(new Request()),
            'http://localhost/report/1/export?expires=60&signature=1234abcd'
        );
    }

}