<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminDetailsTest extends LaravelTestCase
{

    #[Test]
    public function render_shows_sql_to_admins_only(): void
    {
        $report = factory(Report::class)->create();
        factory(ReportMiddleware::class)->create(['report_id' => $report->getKey()]);

        $this->actingAs($this->user(true))
            ->postJson(route('report.render', ['report' => $report->getKey()]))
            ->assertStatus(200)
            ->assertJsonStructure(['raw']);

        $response = $this->actingAs($this->user(false))
            ->postJson(route('report.render', ['report' => $report->getKey()]))
            ->assertStatus(200);

        $this->assertArrayNotHasKey('raw', $response->json());
    }

    #[Test]
    public function job_details_hide_sql_and_error_text_from_non_admins(): void
    {
        $owner = $this->user(false);

        $job = factory(ReportJob::class)->create([
            'authenticatable_id' => $owner->getKey(),
            'status' => JobStatus::FAILED,
            'query' => 'select * from secret_table',
            'exception' => 'SQLSTATE[42S02]: secret_table not found',
        ]);

        $response = $this->actingAs($owner)
            ->getJson(route('report.queue.job', ['job' => $job->getKey()]))
            ->assertStatus(200)
            ->assertJson([
                'uuid' => $job->getKey(),
                'status' => JobStatus::FAILED,
                'exception' => 'The report could not be completed.',
            ]);

        $this->assertArrayNotHasKey('query', $response->json());
        $this->assertStringNotContainsString('secret_table', $response->getContent());
    }

    #[Test]
    public function job_details_are_unchanged_for_admins(): void
    {
        $job = factory(ReportJob::class)->create([
            'status' => JobStatus::FAILED,
            'query' => 'select * from secret_table',
            'exception' => 'SQLSTATE[42S02]: secret_table not found',
        ]);

        $this->actingAs($this->user(true))
            ->getJson(route('report.queue.job', ['job' => $job->getKey()]))
            ->assertStatus(200)
            ->assertJson([
                'uuid' => $job->getKey(),
                'query' => 'select * from secret_table',
                'exception' => 'SQLSTATE[42S02]: secret_table not found',
            ]);
    }

    #[Test]
    public function job_without_an_error_shows_no_error_text(): void
    {
        $owner = $this->user(false);

        $job = factory(ReportJob::class)->create(['authenticatable_id' => $owner->getKey()]);

        $this->actingAs($owner)
            ->getJson(route('report.queue.job', ['job' => $job->getKey()]))
            ->assertStatus(200)
            ->assertJson(['exception' => null]);
    }

    private function user(bool $admin): User
    {
        $user = $this->createFakeUser();
        $user->setIsAdmin($admin);

        return $user;
    }
}
