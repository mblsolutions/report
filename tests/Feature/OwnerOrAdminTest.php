<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class OwnerOrAdminTest extends LaravelTestCase
{
    protected User $owner;

    protected User $other;

    protected User $admin;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        [$this->owner, $this->other, $this->admin] = $this->createFakeUser(3)->all();

        $this->admin->setIsAdmin();
    }

    #[Test]
    public function only_the_owner_or_an_admin_can_view_a_job(): void
    {
        $job = factory(ReportJob::class)->create(['authenticatable_id' => $this->owner->getKey()]);
        $route = route('report.queue.job', ['job' => $job->getKey()]);

        $this->actingAs($this->other)->getJson($route)->assertStatus(404);
        $this->actingAs($this->owner)->getJson($route)->assertStatus(200);
        $this->actingAs($this->admin)->getJson($route)->assertStatus(200);
    }

    #[Test]
    public function only_the_owner_or_an_admin_can_get_job_download_links(): void
    {
        $job = factory(ReportJob::class)->create(['authenticatable_id' => $this->owner->getKey()]);
        $route = route('report.queue.export', ['job' => $job->getKey()]);

        $this->actingAs($this->other)->getJson($route)->assertStatus(404);
        $this->actingAs($this->owner)->getJson($route)->assertStatus(200);
        $this->actingAs($this->admin)->getJson($route)->assertStatus(200);
    }

    #[Test]
    public function only_the_owner_or_an_admin_can_view_a_schedule(): void
    {
        $schedule = factory(ScheduledReport::class)->create(['authenticatable_id' => $this->owner->getKey()]);
        $route = route('report.schedule.show', ['schedule' => $schedule->getKey()]);

        $this->actingAs($this->other)->getJson($route)->assertStatus(404);
        $this->actingAs($this->owner)->getJson($route)->assertStatus(200);
        $this->actingAs($this->admin)->getJson($route)->assertStatus(200);
    }

    #[Test]
    public function only_the_owner_or_an_admin_can_delete_a_schedule(): void
    {
        $first = factory(ScheduledReport::class)->create(['authenticatable_id' => $this->owner->getKey()]);
        $second = factory(ScheduledReport::class)->create(['authenticatable_id' => $this->owner->getKey()]);

        $this->actingAs($this->other)->deleteJson(route('report.schedule.destroy', ['schedule' => $first->getKey()]))->assertStatus(404);
        $this->assertNotSoftDeleted($first);

        $this->actingAs($this->owner)->deleteJson(route('report.schedule.destroy', ['schedule' => $first->getKey()]))->assertStatus(204);
        $this->actingAs($this->admin)->deleteJson(route('report.schedule.destroy', ['schedule' => $second->getKey()]))->assertStatus(204);

        $this->assertSoftDeleted($first);
        $this->assertSoftDeleted($second);
    }

    #[Test]
    public function a_queued_job_has_its_owner_before_it_runs(): void
    {
        Bus::fake();

        $response = $this->actingAs($this->owner)
            ->postJson(route('report.queue.render', ['report' => factory(Report::class)->create()->getKey()]))
            ->assertStatus(202);

        $job = ReportJob::query()->findOrFail($response->json('uuid'));

        $this->assertEquals($this->owner->getKey(), $job->authenticatable_id);

        $this->getJson(route('report.queue.job', ['job' => $job->getKey()]))->assertStatus(200);
    }
}
