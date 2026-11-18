<?php

namespace MBLSolutions\Report\Tests\Unit\Services;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

class ReportTimeoutTest extends LaravelTestCase
{
    private const INIT_COMMAND = 1002;

    protected Report $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        // PDO connects lazily, so no MySQL server is needed
        config([
            'database.connections.reporting' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'reporting',
                'username' => 'reporting',
                'password' => '',
                'options' => [self::INIT_COMMAND => 'SET SESSION sql_mode = ""'],
            ],
            'report.allowed_connections' => ['reporting'],
        ]);

        $this->report = factory(Report::class)->create(['connection' => 'reporting']);
    }

    #[Test]
    public function queued_runs_use_a_copy_of_the_connection_with_the_queued_timeout(): void
    {
        $this->setRunningInConsole(true);

        $service = new BuildReportService($this->report, []);

        $this->assertSame('reporting-report-300', $service->query()->getConnection()->getName());
        $this->assertSame(
            'SET SESSION max_execution_time=300000',
            config('database.connections.reporting-report-300.options')[self::INIT_COMMAND]
        );
    }

    #[Test]
    public function on_screen_runs_use_the_screen_timeout(): void
    {
        $this->setRunningInConsole(false);

        config(['report.timeout.screen' => 45]);

        $service = new BuildReportService($this->report, []);

        $this->assertSame('reporting-report-45', $service->query()->getConnection()->getName());
        $this->assertSame(
            'SET SESSION max_execution_time=45000',
            config('database.connections.reporting-report-45.options')[self::INIT_COMMAND]
        );
    }

    #[Test]
    public function the_original_connection_is_unchanged(): void
    {
        $before = config('database.connections.reporting');

        new BuildReportService($this->report, []);

        $this->assertSame($before, config('database.connections.reporting'));
    }

    #[Test]
    public function other_drivers_use_the_connection_as_it_is(): void
    {
        config(['report.allowed_connections' => ['reporting', 'sqlite']]);

        $service = new BuildReportService(factory(Report::class)->create(), []);

        $this->assertSame('sqlite', $service->query()->getConnection()->getName());
    }

    private function setRunningInConsole(bool $console): void
    {
        (new ReflectionProperty($this->app, 'isRunningInConsole'))->setValue($this->app, $console);
    }
}
