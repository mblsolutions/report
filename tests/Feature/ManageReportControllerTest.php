<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ManageReportControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_view_manage_report_index(): void
    {
        factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson('/report/manage');

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
        factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson('/report/manage/1');

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
        $response = $this->getJson('/report/manage/null');

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
        $response = $this->postJson('/report/manage', [
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
        $response = $this->postJson('/report/manage');

        $response->assertStatus(422);
    }
    
    /** @test **/
    public function can_update_a_report(): void
    {
        factory(Report::class)->create([
            'name' => 'Test Report',
            'description' => 'A test report.',
        ]);

        $response = $this->patchJson('/report/manage/1', [
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
        factory(Report::class)->create([
            'name' => 'Test Report',
            'description' => 'A test report.',
        ]);

        $response = $this->patchJson('/report/manage/1');

        $response->assertStatus(422);
    }

    /** @test **/
    public function can_delete_a_report(): void
    {
        factory(Report::class)->create();

        $response = $this->deleteJson('/report/manage/1');

        $response->assertStatus(200);
    }
    
    /** @test **/
    public function can_test_if_a_report_is_successful(): void
    {
        factory(Report::class)->create();

        $response = $this->postJson('/report/manage/1/test');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                ]);
    }

    /** @test **/
    public function can_test_if_a_report_has_failed(): void
    {
        factory(Report::class)->create([
            'table' => 'invalid_table'
        ]);

        $response = $this->postJson('/report/manage/1/test');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false
                 ]);
    }

}