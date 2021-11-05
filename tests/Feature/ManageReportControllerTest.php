<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ManageReportControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_view_manage_report_index(): void
    {
        $this->withoutExceptionHandling();

        factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.manage.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Test Report',
                    ]
                ],
            ]);
    }
    
    /** @test **/
    public function can_show_manage_report(): void
    {
        $report = factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.manage.show', ['report' => $report->getKey()]));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Test Report',
                ],
            ]);
    }

    /** @test **/
    public function can_show_manage_report_for_new_report(): void
    {
        $response = $this->getJson(route('report.manage.show', ['report' => 'null']));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => null,
                ],
            ]);
    }

    /** @test **/
    public function can_store_a_new_report(): void
    {
        $response = $this->postJson(route('report.manage.store'), [
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ]);

        $response->assertStatus(201);
    }

    /** @test **/
    public function create_missing_or_invalid_data_returns_422(): void
    {
        $response = $this->postJson(route('report.manage.store'));

        $response->assertStatus(422);
    }
    
    /** @test **/
    public function can_update_a_report(): void
    {
        $report = factory(Report::class)->create([
            'name' => 'Test Report',
            'description' => 'A test report.',
        ]);

        $response = $this->patchJson(route('report.manage.update', ['report' => $report->getKey()]), [
            'name' => 'Updated Test Report',
            'description' => 'This report was updated.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ]);

        $response->assertStatus(200);
    }

    /** @test **/
    public function update_missing_or_invalid_data_returns_422(): void
    {
        $report = factory(Report::class)->create([
            'name' => 'Test Report',
            'description' => 'A test report.',
        ]);

        $response = $this->patchJson(route('report.manage.destroy', ['report' => $report->getKey()]));

        $response->assertStatus(422);
    }

    /** @test **/
    public function can_delete_a_report(): void
    {
        $report = factory(Report::class)->create();

        $response = $this->deleteJson(route('report.manage.destroy', ['report' => $report->getKey()]));

        $response->assertStatus(200);
    }
    
    /** @test **/
    public function can_test_if_a_report_is_successful(): void
    {
        $report = factory(Report::class)->create();

        $response = $this->postJson(route('report.manage.test', ['report' => $report->getKey()]));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                ]);
    }

    /** @test **/
    public function can_test_if_a_report_has_failed(): void
    {
        $report = factory(Report::class)->create([
            'table' => 'invalid_table'
        ]);

        $response = $this->postJson(route('report.manage.test', ['report' => $report->getKey()]));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false
                 ]);
    }

}