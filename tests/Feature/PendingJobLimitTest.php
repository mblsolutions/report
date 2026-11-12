<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class PendingJobLimitTest extends LaravelTestCase
{
    protected User $user;

    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->user = $this->createFakeUser();
        $this->report = factory(Report::class)->create();
    }

    #[Test]
    public function the_sixth_pending_run_is_refused(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->queue()->assertStatus(202);
        }

        $this->queue()->assertStatus(429);

        $this->assertSame(5, ReportJob::query()->count());
    }

    #[Test]
    public function finished_old_and_other_users_jobs_do_not_count(): void
    {
        $other = $this->createFakeUser();

        foreach ([JobStatus::COMPLETE, JobStatus::FAILED] as $status) {
            factory(ReportJob::class, 5)->create(['authenticatable_id' => $this->user->getKey(), 'status' => $status]);
        }

        factory(ReportJob::class, 5)->create(['authenticatable_id' => $other->getKey(), 'status' => JobStatus::RUNNING]);
        factory(ReportJob::class, 5)->create([
            'authenticatable_id' => $this->user->getKey(),
            'status' => JobStatus::RUNNING,
            'created_at' => Carbon::now()->subMinutes(61),
        ]);

        $this->queue()->assertStatus(202);
    }

    #[Test]
    public function the_limit_can_be_changed(): void
    {
        config(['report.max_pending_jobs' => 1]);

        $this->queue()->assertStatus(202);
        $this->queue()->assertStatus(429);
    }

    private function queue()
    {
        return $this->actingAs($this->user)->postJson(route('report.queue.render', ['report' => $this->report->getKey()]));
    }
}
