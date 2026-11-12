<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Repositories\ManageReportRepository;
use MBLSolutions\Report\Repositories\ReportJobRepository;
use MBLSolutions\Report\Repositories\ReportRepository;
use MBLSolutions\Report\Repositories\ScheduledReportRepository;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class PageSizeTest extends LaravelTestCase
{
    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsAdmin();
    }

    #[Test]
    public function list_pages_hold_at_most_100_rows(): void
    {
        $this->assertSame(100, (new ReportRepository())->paginate(100000)->perPage());
        $this->assertSame(100, (new ReportJobRepository())->paginate(100000)->perPage());
        $this->assertSame(100, (new ScheduledReportRepository())->paginate(100000)->perPage());
        $this->assertSame(100, (new ManageReportRepository())->paginate(100000)->perPage());

        $this->getJson(route('report.index', ['limit' => 100000]))->assertStatus(200)->assertJsonPath('meta.per_page', 100);
    }

    #[Test]
    public function smaller_page_sizes_still_work(): void
    {
        $this->assertSame(10, (new ReportRepository())->paginate(10)->perPage());
        $this->assertSame(25, (new ReportRepository())->paginate()->perPage());
    }

    #[Test]
    public function the_pending_list_holds_at_most_100_jobs(): void
    {
        factory(ReportJob::class, 101)->create(['report_id' => factory(Report::class)->create()->getKey()]);

        $this->assertCount(100, (new ReportJobRepository())->pendingJobs(100000));
    }
}
