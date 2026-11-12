<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ManagementSwitchTest extends LaravelTestCase
{
    protected array $body = [
        'name' => 'New Report',
        'description' => 'A test report.',
        'connection' => 'sqlite',
        'display_limit' => 25,
        'show_data' => true,
        'show_totals' => false,
        'active' => true,
        'table' => 'users',
    ];

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsAdmin();
    }

    #[Test]
    public function report_editing_is_off_by_default(): void
    {
        $report = factory(Report::class)->create(['name' => 'Old Name']);

        $this->postJson(route('report.manage.store'), $this->body)->assertStatus(403);
        $this->patchJson(route('report.manage.update', ['report' => $report->getKey()]), $this->body)->assertStatus(403);
        $this->deleteJson(route('report.manage.destroy', ['report' => $report->getKey()]))->assertStatus(403);
        $this->postJson(route('report.manage.test', ['report' => $report->getKey()]))->assertStatus(403);

        $this->assertSame(1, Report::query()->count());
        $this->assertSame('Old Name', $report->refresh()->name);
        $this->assertNull($report->deleted_at);
    }

    #[Test]
    public function reports_can_still_be_listed_and_shown(): void
    {
        $report = factory(Report::class)->create();

        $this->getJson(route('report.manage.index'))->assertStatus(200);
        $this->getJson(route('report.manage.show', ['report' => $report->getKey()]))->assertStatus(200);
    }

    #[Test]
    public function report_editing_works_when_enabled(): void
    {
        config(['report.management_enabled' => true]);

        $this->postJson(route('report.manage.store'), $this->body)->assertStatus(201);

        $this->assertSame(1, Report::query()->count());
    }
}
