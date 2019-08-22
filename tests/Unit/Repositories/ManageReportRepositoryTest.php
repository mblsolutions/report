<?php

namespace MBLSolutions\Report\Tests\Unit\Repositories;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Events\ReportCreated;
use MBLSolutions\Report\Events\ReportDestroyed;
use MBLSolutions\Report\Events\ReportUpdated;
use MBLSolutions\Report\Middleware\Authenticated;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Repositories\ManageReportRepository;
use MBLSolutions\Report\Tests\Fakes\Middleware\FakeMiddleware;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ManageReportRepositoryTest extends LaravelTestCase
{
    /** @var ManageReportRepository $repository */
    public $repository;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ManageReportRepository;
    }

    /** @test **/
    public function can_store_a_new_report(): void
    {
        $request = new Request([
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
            'middleware' => [
                ['middleware' => FakeMiddleware::class]
            ],
            'fields' => [
                [
                    'label' => 'Users',
                    'type' => 'integer',
                    'model' => User::class,
                    'alias' => 'users',
                    'model_select_value' => 'id',
                    'model_select_name' => 'name'
                ]
            ],
            'selects' => [
                [
                    'column' => 'name',
                    'alias' => 'user_name',
                    'type' => CastTitleCaseString::class,
                    'column_order' => 0,
                ]
            ],
            'joins' => [
                [
                    'type' => 'left_join',
                    'table' => 'posts',
                    'first' => 'users.id',
                    'operator' => '=',
                    'second' => 'posts.user_id'
                ]
            ],
        ]);

        $this->repository->create($request);

        $this->assertDatabaseHas('reports', [
            'id' => 1,
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ]);

        $this->assertDatabaseHas('report_middleware', [
            'id' => 1,
            'report_id' => 1,
            'middleware' => FakeMiddleware::class,
            'deleted_at' => null
        ]);

        $this->assertDatabaseHas('report_fields', [
            'id' => 1,
            'report_id' => 1,
            'label' => 'Users',
            'type' => 'integer',
            'model' => User::class,
            'alias' => 'users',
            'model_select_value' => 'id',
            'model_select_name' => 'name',
            'deleted_at' => null
        ]);

        $this->assertDatabaseHas('report_selects', [
            'id' => 1,
            'report_id' => 1,
            'column' => 'name',
            'alias' => 'user_name',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        $this->assertDatabaseHas('report_joins', [
            'id' => 1,
            'report_id' => 1,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);
    }

    /** @test **/
    public function storing_a_new_report_fires_event(): void
    {
        Event::fake();

        $request = new Request([
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ]);

        $this->repository->create($request);

        Event::assertDispatched(ReportCreated::class, static function ($event) {
            return $event->report->id === Report::first()->id;
        });
    }

    /** @test **/
    public function can_update_a_report(): void
    {
        $report = factory(Report::class)->create();

        factory(ReportSelect::class)->create([
            'report_id' => $report->id,
            'column' => 'name',
            'alias' => 'user_name',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        factory(ReportJoin::class)->create([
            'report_id' => $report->id,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        factory(ReportMiddleware::class)->create([
            'report_id' => $report->id,
            'middleware' => FakeMiddleware::class
        ]);

        factory(ReportField::class)->create([
            'report_id' => $report->id,
            'label' => 'Created At',
            'type' => 'datetime',
            'model' => null,
            'alias' => 'users_created_date',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $request = new Request([
            'name' => 'Updated Test Report',
            'description' => 'This report was updated.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
            'middleware' => [
                ['middleware' => Authenticated::class]
            ],
            'fields' => [
                [
                    'id' => 1,
                    'label' => 'Select User',
                    'type' => 'integer',
                    'model' => User::class,
                    'alias' => 'users',
                    'model_select_value' => 'id',
                    'model_select_name' => 'name'
                ]
            ],
            'selects' => [
                [
                    'id' => 1,
                    'column' => 'User Name',
                    'alias' => 'user_name',
                    'type' => CastTitleCaseString::class,
                    'column_order' => 0,
                ]
            ],
            'joins' => [
                [
                    'id' => 1,
                    'type' => 'left_join',
                    'table' => 'comments',
                    'first' => 'users.id',
                    'operator' => '=',
                    'second' => 'comments.user_id',
                    'deleted_at' => '2019-01-01 00:00:00'
                ]
            ],
        ]);

        $this->repository->update($report, $request);

        $this->assertDatabaseHas('reports', [
            'name' => 'Updated Test Report',
            'description' => 'This report was updated.'
        ]);

        $this->assertDatabaseHas('report_fields', [
            'id' => 1,
            'report_id' => 1,
            'label' => 'Select User',
            'type' => 'integer',
            'model' => User::class,
            'alias' => 'users',
            'model_select_value' => 'id',
            'model_select_name' => 'name',
            'deleted_at' => null
        ]);

        $this->assertDatabaseHas('report_selects', [
            'id' => 1,
            'report_id' => 1,
            'column' => 'User Name',
            'alias' => 'user_name',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        $this->assertDatabaseHas('report_joins', [
            'id' => 1,
            'report_id' => 1,
            'type' => 'left_join',
            'table' => 'comments',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'comments.user_id',
            'deleted_at' => '2019-01-01 00:00:00'
        ]);
    }
    
    /** @test **/
    public function updating_a_new_report_fires_event(): void
    {
        Event::fake();

        $report = factory(Report::class)->create();

        $request = new Request([
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ]);

        $this->repository->update($report, $request);

        Event::assertDispatched(ReportUpdated::class, static function ($event) {
            return $event->report->id === Report::first()->id;
        });
    }

    /** @test **/
    public function can_delete_a_report(): void
    {
        $report = factory(Report::class)->create();

        $this->repository->delete($report);

        $this->assertDatabaseHas('reports', [
            'id' => 1,
            'deleted_at' => now()->toDateTimeString()
        ]);
    }


    /** @test **/
    public function deleting_a_report_fires_event(): void
    {
        Event::fake();

        $report = factory(Report::class)->create();

        $this->repository->delete($report);

        Event::assertDispatched(ReportDestroyed::class, static function ($event) {
            return $event->report->id === Report::withTrashed()->first()->id;
        });
    }

    /** @test */
    public function when_creating_a_report_if_exception_is_thrown_on_relation_transaction_is_rolled_back(): void
    {
        $request = new Request([
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
            'middleware' => [
                ['middleware' => FakeMiddleware::class]
            ],
            'fields' => [
                [
                    'label' => 'Users',
                    // Missing required parameters for database
                ]
            ],
            'selects' => [
                [
                    'column' => 'name',
                    'alias' => 'user_name',
                    'type' => CastTitleCaseString::class,
                    'column_order' => 0,
                ]
            ],
            'joins' => [
                [
                    'type' => 'left_join',
                    'table' => 'posts',
                    'first' => 'users.id',
                    'operator' => '=',
                    'second' => 'posts.user_id'
                ]
            ],
        ]);

        try {
            $this->repository->create($request);
        } catch (QueryException $exception) {
        }

        $this->assertDatabaseMissing('reports', [
            'id' => 1,
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
        ]);

        $this->assertDatabaseMissing('report_middleware', [
            'id' => 1,
            'report_id' => 1,
        ]);

        $this->assertDatabaseMissing('report_fields', [
            'id' => 1,
            'report_id' => 1,
        ]);

        $this->assertDatabaseMissing('report_selects', [
            'id' => 1,
            'report_id' => 1,
        ]);

        $this->assertDatabaseMissing('report_joins', [
            'id' => 1,
            'report_id' => 1,
        ]);
    }

    /** @test */
    public function when_updating_a_report_if_exception_is_thrown_on_relation_transaction_is_rolled_back(): void
    {
        $report = factory(Report::class)->create([
            'name' => 'Test Report',
            'description' => 'A Test Report.'
        ]);

        factory(ReportSelect::class)->create([
            'report_id' => $report->id,
            'column' => 'name',
            'alias' => 'user_name',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        factory(ReportJoin::class)->create([
            'report_id' => $report->id,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        factory(ReportMiddleware::class)->create([
            'report_id' => $report->id,
            'middleware' => FakeMiddleware::class
        ]);

        factory(ReportField::class)->create([
            'report_id' => $report->id,
            'label' => 'Created At',
            'type' => 'datetime',
            'model' => null,
            'alias' => 'users_created_date',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $request = new Request([
            'name' => 'Updated Test Report',
            'description' => 'This report was updated.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
            'middleware' => [
                ['middleware' => Authenticated::class]
            ],
            'fields' => [
                [
                    'id' => 1,
                    'label' => null,
                    'type' => 'integer',
                    'model' => User::class,
                    'alias' => 'users',
                    'model_select_value' => 'id',
                    'model_select_name' => 'name'
                ]
            ],
            'selects' => [
                [
                    'id' => 1,
                    'column' => 'User Name',
                    'alias' => 'user_name',
                    'type' => CastTitleCaseString::class,
                    'column_order' => 0,
                ]
            ],
            'joins' => [
                [
                    'id' => 1,
                    'type' => 'left_join',
                    'table' => 'comments',
                    'first' => 'users.id',
                    'operator' => '=',
                    'second' => 'comments.user_id',
                    'deleted_at' => '2019-01-01 00:00:00'
                ]
            ],
        ]);

        try {
            $this->repository->update($report, $request);
        } catch (QueryException $exception) {
        }


        $this->assertDatabaseHas('reports', [
            'name' => 'Test Report',
            'description' => 'A Test Report.'
        ]);

        $this->assertDatabaseHas('report_fields', [
            'id' => 1,
            'report_id' => 1,
            'label' => 'Created At',
            'type' => 'datetime',
            'model' => null,
            'alias' => 'users_created_date',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $this->assertDatabaseHas('report_selects', [
            'id' => 1,
            'report_id' => 1,
            'column' => 'name',
            'alias' => 'user_name',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        $this->assertDatabaseHas('report_joins', [
            'id' => 1,
            'report_id' => 1,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);
    }

}