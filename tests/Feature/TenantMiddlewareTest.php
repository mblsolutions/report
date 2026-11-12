<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use MBLSolutions\Report\Exceptions\UnauthorizedException;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Middleware\Authenticated;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Tests\Fakes\Middleware\FakeMiddleware;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class TenantMiddlewareTest extends LaravelTestCase
{
    protected Report $authenticatedOnly;

    protected Report $tenantScoped;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        config([
            'report.tenant_middleware' => [FakeMiddleware::class],
            'report.authenticatable_model' => User::class,
        ]);

        $this->authenticatedOnly = factory(Report::class)->create();
        factory(ReportMiddleware::class)->create([
            'report_id' => $this->authenticatedOnly->getKey(),
            'middleware' => Authenticated::class,
        ]);

        $this->tenantScoped = factory(Report::class)->create();
        factory(ReportMiddleware::class)->create(['report_id' => $this->tenantScoped->getKey()]);
    }

    #[Test]
    public function non_admins_cannot_run_a_report_without_a_tenant_middleware(): void
    {
        $this->actingAs($this->user(false))
            ->postJson(route('report.render', ['report' => $this->authenticatedOnly->getKey()]))
            ->assertStatus(403);
    }

    #[Test]
    public function non_admins_can_run_a_report_with_a_tenant_middleware(): void
    {
        $this->actingAs($this->user(false))
            ->postJson(route('report.render', ['report' => $this->tenantScoped->getKey()]))
            ->assertStatus(200);
    }

    #[Test]
    public function admins_can_run_a_report_without_a_tenant_middleware(): void
    {
        $this->actingAs($this->user(true))
            ->postJson(route('report.render', ['report' => $this->authenticatedOnly->getKey()]))
            ->assertStatus(200);
    }

    #[Test]
    public function an_empty_tenant_list_refuses_every_non_admin_run(): void
    {
        config(['report.tenant_middleware' => []]);

        $this->actingAs($this->user(false))
            ->postJson(route('report.render', ['report' => $this->tenantScoped->getKey()]))
            ->assertStatus(403);
    }

    #[Test]
    public function the_check_covers_every_path_that_builds_the_query(): void
    {
        $this->actingAs($this->user(false));

        $this->expectException(UnauthorizedException::class);

        (new BuildReportService($this->authenticatedOnly, []))->buildReportQuery();
    }

    #[Test]
    public function a_job_whose_owner_is_gone_fails_without_rows(): void
    {
        $owner = $this->user(true);
        $owner->delete();

        $this->runJob($this->tenantScoped, $owner->getKey());

        $this->assertJobStatus(JobStatus::FAILED);
        $this->assertSame([], Storage::allFiles());
    }

    #[Test]
    public function a_queued_job_owned_by_an_admin_completes(): void
    {
        $this->runJob($this->authenticatedOnly, $this->user(true)->getKey());

        $this->assertJobStatus(JobStatus::COMPLETE);
    }

    #[Test]
    public function a_queued_job_owned_by_a_non_admin_fails(): void
    {
        $this->runJob($this->authenticatedOnly, $this->user(false)->getKey());

        $this->assertJobStatus(JobStatus::FAILED);
        $this->assertSame([], Storage::allFiles());
    }

    private function runJob(Report $report, $owner): void
    {
        $this->app['auth']->forgetGuards();

        try {
            dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $report, [], $owner));
        } catch (Throwable) {
        }
    }

    private function assertJobStatus(string $status): void
    {
        $this->assertSame($status, ReportJob::query()->findOrFail('aa5615b7-8489-4831-ad00-f50ae770b619')->status);
    }

    private function user(bool $admin): User
    {
        $user = $this->createFakeUser();
        $user->setIsAdmin($admin);

        return $user;
    }
}
