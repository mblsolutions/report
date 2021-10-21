<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ReportControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_view_report_index(): void
    {
        factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.index'));

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         [
                             'name' => 'Test Report',
                         ]
                     ],
                 ]);
    }

    /** @test **/
    public function can_show_report(): void
    {
        $report = factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.show', ['report' => $report->getKey()]));

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'name' => 'Test Report',
                     ],
                 ]);
    }

    /** @test **/
    public function can_render_a_report(): void
    {
        $report = factory(Report::class)->create();

        $response = $this->postJson(route('report.render', ['report' => $report->getKey()]));

        $response->assertStatus(200);
    }
    
    /** @test **/
    public function can_generate_report_export_uri(): void
    {
        $report = factory(Report::class)->create();

        $response = $this->actingAs(new User)->postJson(route('report.export', ['report' => $report->getKey()]));

        $response->assertStatus(200);
    }

    /** @test **/
    public function can_export_report(): void
    {
        $report = factory(Report::class)->create();

        $request = new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]);

        $response = $this->actingAs(new User)->get($report->getExportLink($request));

        $response->assertStatus(200);
    }

    /** @test **/
    public function can_export_report_authorises_use(): void
    {
        Auth::login(new User);

        $report = factory(Report::class)->create();

        $request = new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]);

        $response = $this->get($report->getExportLink($request));

        $response->assertStatus(200);
    }

    /** @test **/
    public function export_report_with_invalid_or_modified_signature_returns_unauthorized(): void
    {
        $report = factory(Report::class)->create();

        $request = new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]);

        $response = $this->actingAs(new User)->get($report->getExportLink($request) . '&uuid=1');

        $response->assertStatus(401);
    }

}