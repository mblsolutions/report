<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MBLSolutions\Report\DataType\CastString;
use MBLSolutions\Report\Driver\Export\CsvExport;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Support\Builder\ChunkIncrementalResults;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class RowCapTest extends LaravelTestCase
{
    protected User $admin;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createFakeUser();
        $this->admin->setIsAdmin();

        $this->createFakeUser(4);
    }

    #[Test]
    public function a_direct_export_over_the_cap_is_refused(): void
    {
        config(['report.max_rows.export' => 3]);

        $this->actingAs($this->admin)
            ->getJson($this->exportLink())
            ->assertStatus(422)
            ->assertJson(['message' => 'This report has more than 3 rows. Use the queued export.']);
    }

    #[Test]
    public function a_direct_export_at_the_cap_works(): void
    {
        config(['report.max_rows.export' => 5]);

        $this->actingAs($this->admin)->get($this->exportLink())->assertStatus(200);
    }

    #[Test]
    public function a_direct_export_counts_result_rows_not_source_rows(): void
    {
        config(['report.max_rows.export' => 3]);

        $this->actingAs($this->admin);

        // One result row from 5 source rows
        $report = factory(Report::class)->create();
        factory(ReportSelect::class)->create([
            'report_id' => $report->getKey(),
            'column' => 'COUNT(DISTINCT users.id)',
            'alias' => 'users',
            'type' => CastString::class,
        ]);

        $link = $report->getExportLink(new Request([
            'driver' => ReportExportDrivers::hashDriverValue(CsvExport::class),
        ]));

        $this->assertSame("users\n5\n", $this->get($link)->assertStatus(200)->streamedContent());
    }

    #[Test]
    public function a_direct_export_over_the_cap_is_refused_when_grouped(): void
    {
        config(['report.max_rows.export' => 3]);

        $this->actingAs($this->admin);

        $report = factory(Report::class)->create(['groupby' => 'users.id']);
        factory(ReportSelect::class)->create([
            'report_id' => $report->getKey(),
            'column' => 'COUNT(*)',
            'alias' => 'rows',
            'type' => CastString::class,
        ]);

        $link = $report->getExportLink(new Request([
            'driver' => ReportExportDrivers::hashDriverValue(CsvExport::class),
        ]));

        $this->getJson($link)->assertStatus(422);
    }

    #[Test]
    public function a_screen_page_past_the_cap_is_refused(): void
    {
        config(['report.max_rows.export' => 50]);

        $report = factory(Report::class)->create(['display_limit' => 25]);

        $this->actingAs($this->admin);

        $this->postJson(route('report.render', ['report' => $report->getKey(), 'page' => 2]))->assertStatus(200);
        $this->postJson(route('report.render', ['report' => $report->getKey(), 'page' => 3]))
            ->assertStatus(422)
            ->assertJson(['message' => 'This report has more than 50 rows. Use the queued export.']);
    }

    #[Test]
    public function queued_runs_have_no_row_cap(): void
    {
        Storage::fake();

        config(['report.max_rows.export' => 3, 'report.authenticatable_model' => User::class]);

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', factory(Report::class)->create(), [], $this->admin->getKey()));

        $job = ReportJob::query()->findOrFail('aa5615b7-8489-4831-ad00-f50ae770b619');

        $this->assertSame(JobStatus::COMPLETE, $job->status);
        $this->assertSame(5, $job->total);
    }

    #[Test]
    public function the_row_count_does_not_select_every_column(): void
    {
        DB::enableQueryLog();

        $count = (new ChunkIncrementalResults(DB::table('users'), 3))->getTotalResultsForChunk(0);

        $this->assertSame(3, $count);
        $this->assertStringNotContainsString('*', DB::getQueryLog()[0]['query']);
    }

    private function exportLink(): string
    {
        $this->actingAs($this->admin);

        return factory(Report::class)->create()->getExportLink(new Request([
            'driver' => ReportExportDrivers::hashDriverValue(CsvExport::class),
        ]));
    }
}
