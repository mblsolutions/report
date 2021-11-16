<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Report\RenderJobUuidGenerator;
use MBLSolutions\Report\Tests\LaravelTestCase;

class QueuedReportControllerTest extends LaravelTestCase
{

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Excel::fake();
        Bus::fake();
    }

    /** @test **/
    public function can_view_report_queue_index(): void
    {
        $this->getJson(route('report.queue.index'))->assertStatus(200);
    }
    
    /** @test **/
    public function can_render_a_queued_report(): void
    {
        $data = new Collection([
            'uuid' => '931c1666-a70f-422b-aefe-925ab8b59aeb',
            'href' => route('report.queue.job', ['job' => '931c1666-a70f-422b-aefe-925ab8b59aeb']),
        ]);

        $this->app->instance(RenderJobUuidGenerator::class, function () use ($data) {
            return $data;
        });

        $this->postJson(route('report.queue.render', [
            'report' => factory(Report::class)->create()->getKey()
        ]))
        ->assertStatus(202)
        ->assertJson($data->toArray());
    }
    
    /** @test **/
    public function rendering_a_queued_report_dispatches_render_report_job(): void
    {
        $this->postJson(route('report.queue.render', [
            'report' => $report = factory(Report::class)->create()
        ]));

        Bus::assertDispatched(RenderReport::class, function ($job) use ($report) {
            return $job->report->getKey() === $report->getKey();
        });
    }

    /** @test **/
    public function can_view_job_status(): void
    {
        factory(ReportJob::class)->create([
            'uuid' => '931c1666-a70f-422b-aefe-925ab8b59aeb'
        ]);

        $this->getJson(route('report.queue.job', [
            'job' => '931c1666-a70f-422b-aefe-925ab8b59aeb'
        ]))->assertStatus(200);
    }

    /** @test **/
    public function can_generate_export_link(): void
    {
        Storage::fake();

        factory(ReportJob::class)->create([
            'uuid' => '931c1666-a70f-422b-aefe-925ab8b59aeb'
        ]);

        $this->getJson(route('report.queue.export', ['job' => '931c1666-a70f-422b-aefe-925ab8b59aeb']))->assertStatus(200);
    }

}