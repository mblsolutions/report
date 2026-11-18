<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use MBLSolutions\Report\Exceptions\UnauthorizedException;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Repositories\ManageReportRepository;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportConnectionTest extends LaravelTestCase
{
    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.other' => config('database.connections.sqlite'),
            'database.connections.fixed' => config('database.connections.sqlite'),
        ]);

        $admin = $this->createFakeUser();
        $admin->setIsAdmin();

        $this->actingAs($admin);
    }

    #[Test]
    public function a_connection_that_is_not_allowed_is_refused(): void
    {
        $report = factory(Report::class)->create(['connection' => 'other']);

        $this->postJson(route('report.render', ['report' => $report->getKey()]))->assertStatus(403);

        $this->expectException(UnauthorizedException::class);

        new BuildReportService($report, []);
    }

    #[Test]
    public function an_allowed_connection_is_used(): void
    {
        config(['report.allowed_connections' => ['sqlite', 'other']]);

        $service = new BuildReportService(factory(Report::class)->create(['connection' => 'other']), []);

        $this->assertSame('other', $service->query()->getConnection()->getName());
    }

    #[Test]
    public function the_fixed_connection_wins(): void
    {
        config(['report.connection' => 'fixed']);

        $service = new BuildReportService(factory(Report::class)->create(['connection' => 'sqlite']), []);

        $this->assertSame('fixed', $service->query()->getConnection()->getName());
    }

    #[Test]
    public function a_grouped_preview_runs_on_the_report_connection(): void
    {
        config(['report.allowed_connections' => ['other']]);

        $report = factory(Report::class)->create(['connection' => 'other', 'groupby' => 'users.name']);
        factory(ReportSelect::class)->create(['report_id' => $report->getKey(), 'column' => 'users.name', 'alias' => 'user_name']);

        $query = (new BuildReportService($report, [], false))->buildReportQuery([], true);

        $this->assertStringContainsString('query_data', $query->toSql());
        $this->assertSame('other', $query->getConnection()->getName());
    }

    #[Test]
    public function saving_a_report_refuses_a_connection_that_is_not_allowed(): void
    {
        $repository = new ManageReportRepository();
        $report = [
            'name' => 'Test Report',
            'description' => 'A test report.',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ];

        $this->assertTrue($repository->validateReportRequest(new Request($report + ['connection' => 'other']))->errors()->has('connection'));
        $this->assertFalse($repository->validateReportRequest(new Request($report + ['connection' => 'sqlite']))->fails());
    }

    #[Test]
    public function the_connection_list_shows_allowed_connections_only(): void
    {
        $values = collect($this->getJson(route('report.connection.list'))->assertStatus(200)->json())->pluck('value')->all();

        $this->assertSame(['sqlite'], array_values($values));
    }
}
