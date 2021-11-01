<?php

namespace MBLSolutions\Report\Tests\Unit\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Driver\Export\CsvExport;
use MBLSolutions\Report\Driver\Export\JsonExport;
use MBLSolutions\Report\Driver\Export\PrintExport;
use MBLSolutions\Report\Events\ReportRendered;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;

class BuildReportServiceTest extends LaravelTestCase
{
    /** @var Report $report */
    protected $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->report = factory(Report::class)->create([
            'table' => 'users',
            'where' => null,
            'groupby' => null,
            'having' => null,
            'orderby' => null,
        ]);
    }

    /** @test **/
    public function can_render_a_report(): void
    {
        $service = new BuildReportService($this->report, []);

        $this->assertInstanceOf(Collection::class, $service->render());
    }

    /** @test **/
    public function rendering_a_report_dispatches_event(): void
    {
        Event::fake();

        $service = new BuildReportService($this->report, []);

        $service->render();

        Event::assertDispatched(ReportRendered::class, static function ($event) {
            return $event->report->id === Report::first()->id;
        });
    }

    /** @test **/
    public function can_get_raw_query(): void
    {
        $service = new BuildReportService($this->report, []);

        $this->assertEquals('select * from "users"', $service->getRawQuery());
    }

    /** @test **/
    public function can_get_export_drivers(): void
    {
        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'exportDrivers');
        $method->setAccessible(true);

        $this->assertEquals([
            (new CsvExport)->name,
            'Fake Export Driver',
            (new JsonExport)->name,
            (new PrintExport)->name
        ], $method->invoke($service)->pluck('name')->toArray());
    }

    /** @test **/
    public function can_get_headings(): void
    {
        factory(ReportSelect::class)->create([
            'report_id' => $this->report->id,
            'column' => 'name',
            'alias' => 'user_name',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        $service = new BuildReportService($this->report, []);

        $this->assertEquals([
            'user_name'
        ], $service->headings()->toArray());
    }

    /** @test **/
    public function can_get_report_selects(): void
    {
        $selects = factory(ReportSelect::class, 3)->create([
            'report_id' => $this->report->id,
        ]);

        $service = new BuildReportService($this->report, []);

        $this->assertEquals($selects->pluck('id')->toArray(), $service->selects()->pluck('id')->toArray());
    }

    /** @test **/
    public function can_get_query(): void
    {
        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'query');
        $method->setAccessible(true);

        $this->assertInstanceOf(Builder::class, $method->invoke($service));
    }

    /** @test **/
    public function can_add_selects(): void
    {
        factory(ReportSelect::class)->create([
            'report_id' => $this->report->id,
            'column' => 'users.id',
            'alias' => 'User ID'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'addSelects');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals('select users.id AS \'User ID\' from "users"', $service->getRawQuery());
    }

    /** @test **/
    public function can_add_joins(): void
    {
        factory(ReportJoin::class)->create([
            'report_id' => $this->report->id,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'addJoins');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals(
            'select * from "users" left join "posts" on "users"."id" = "posts"."user_id"',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_add_where(): void
    {
        $this->report->update([
            'where' => 'users.created_at >= \'2019-01-01 00:00:00\''
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'addWhere');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals(
            'select * from "users" where users.created_at >= \'2019-01-01 00:00:00\'',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_add_group_by(): void
    {
        $this->report->update([
            'groupby' => 'users.created_at'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'addGroupBy');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals(
            'select * from "users" group by users.created_at',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_add_having(): void
    {
        $this->report->update([
            'having' => 'users.id > 100'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'addHaving');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals(
            'select * from "users" having users.id > 100',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_add_order_by(): void
    {
        $this->report->update([
            'orderby' => 'users.created_at desc'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'addOrderBy');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals(
            'select * from "users" order by users.created_at desc',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_check_where_is_not_empty(): void
    {
        $this->report->update([
            'where' => 'users.created_at >= \'2019-01-01 00:00:00\''
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'checkWhereIsNotEmpty');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service));
    }

    /** @test **/
    public function can_check_where_is_empty(): void
    {
        $this->report->update([
            'where' => ''
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'checkWhereIsNotEmpty');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service));
    }

    /** @test **/
    public function can_check_where_is_empty_if_null(): void
    {
        $this->report->update([
            'where' => null
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'checkWhereIsNotEmpty');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service));
    }

    /** @test **/
    public function can_check_where_is_empty_with_spaces(): void
    {
        $this->report->update([
            'where' => '    '
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'checkWhereIsNotEmpty');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service));
    }

    /** @test **/
    public function can_clean_where_syntax_with_remaining_and(): void
    {
        $this->report->update([
            'where' => ' AND'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'cleanWhereSyntax');
        $method->setAccessible(true);

        $this->assertEquals('', $method->invoke($service));
    }

    /** @test **/
    public function can_clean_where_syntax_with_remaining_or(): void
    {
        $this->report->update([
            'where' => ' OR'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'cleanWhereSyntax');
        $method->setAccessible(true);

        $this->assertEquals('', $method->invoke($service));
    }

    /** @test **/
    public function can_replace_parameters(): void
    {
        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'replaceParameter');
        $method->setAccessible(true);

        $this->assertEquals(
            'users.created_at > \'2019-01-01 00:00:00\'',
            $method->invoke(
                $service,
                'users_created_at',
                '2019-01-01 00:00:00', 'users.created_at > \'{users_created_at}\''
            )
        );
    }

    /** @test **/
    public function can_replace_parameter_if_supplied_null(): void
    {
        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'replaceParameter');
        $method->setAccessible(true);

        $this->assertEquals(
            '',
            $method->invoke(
                $service,
                'users_created_at',
                null, 'users.created_at > \'{users_created_at}\''
            )
        );
    }

    /** @test **/
    public function can_replace_parameter_if_supplied_null_with_extended_time_stamp(): void
    {
        $report = factory(Report::class)->create([
            'table' => 'users',
            'where' => "users.id = '{date_field} 00:00:00'",
            'groupby' => null,
            'having' => null,
            'orderby' => null,
        ]);

        factory(ReportSelect::class)->create([
            'report_id' => $report->id,
            'column' => 'users.id',
            'alias' => 'user_id',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        factory(ReportField::class)->create([
            'report_id' => $report->id,
            'label' => 'Date Field',
            'type' => 'date',
            'model' => null,
            'alias' => 'date_field',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $service = new BuildReportService($report, []);

        $service->render();

        $this->assertEquals("select users.id AS 'user_id' from \"users\"", $service->getRawQuery());
    }

    /** @test **/
    public function replace_parameters_that_are_supplied(): void
    {
        $report = factory(Report::class)->create([
            'table' => 'users',
            'where' => "users.id = '{first_field}'",
            'groupby' => null,
            'having' => null,
            'orderby' => null,
        ]);

        factory(ReportSelect::class)->create([
            'report_id' => $report->id,
            'column' => 'users.id',
            'alias' => 'user_id',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        factory(ReportField::class)->create([
            'report_id' => $report->id,
            'label' => 'First Field',
            'type' => 'string',
            'model' => null,
            'alias' => 'first_field',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $service = new BuildReportService($report, ['first_field' => 'replaced_value']);

        $service->render();

        $this->assertEquals("select users.id AS 'user_id' from \"users\" where users.id = 'replaced_value'", $service->getRawQuery());
    }

    /** @test **/
    public function replace_parameters_that_are_not_supplied(): void
    {
        $report = factory(Report::class)->create([
            'table' => 'users',
            'where' => "users.id = '{first_field}'",
            'groupby' => null,
            'having' => null,
            'orderby' => null,
        ]);

        factory(ReportSelect::class)->create([
            'report_id' => $report->id,
            'column' => 'users.id',
            'alias' => 'user_id',
            'type' => CastTitleCaseString::class,
            'column_order' => 0,
        ]);

        factory(ReportField::class)->create([
            'report_id' => $report->id,
            'label' => 'First Field',
            'type' => 'string',
            'model' => null,
            'alias' => 'first_field',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $service = new BuildReportService($report, []);

        $service->render();

        $this->assertEquals("select users.id AS 'user_id' from \"users\"", $service->getRawQuery());
    }

    /** @test **/
    public function can_handle_middleware(): void
    {
        factory(ReportMiddleware::class)->create([
            'report_id' => $this->report->id
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'handleMiddleware');
        $method->setAccessible(true);

        $method->invoke($service);

        $this->assertEquals('select * from "users" where users.id < 100', $service->getRawQuery());
    }

    /** @test **/
    public function can_build_left_join(): void
    {
        $join = factory(ReportJoin::class)->create([
            'report_id' => $this->report->id,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'buildJoin');
        $method->setAccessible(true);

        $method->invoke($service, $join);

        $this->assertEquals(
            'select * from "users" left join "posts" on "users"."id" = "posts"."user_id"',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_build_right_join(): void
    {
        $join = factory(ReportJoin::class)->create([
            'report_id' => $this->report->id,
            'type' => 'right_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'buildJoin');
        $method->setAccessible(true);

        $method->invoke($service, $join);

        $this->assertEquals(
            'select * from "users" right join "posts" on "users"."id" = "posts"."user_id"',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_build_inner_join(): void
    {
        $join = factory(ReportJoin::class)->create([
            'report_id' => $this->report->id,
            'type' => 'join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        $service = new BuildReportService($this->report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'buildJoin');
        $method->setAccessible(true);

        $method->invoke($service, $join);

        $this->assertEquals(
            'select * from "users" inner join "posts" on "users"."id" = "posts"."user_id"',
            $service->getRawQuery()
        );
    }

    /** @test **/
    public function can_build_report_query(): void
    {
        $this->report->update([
            'where' => 'users.created_at >= \'{users_created_date}\'',
            'groupby' => 'users.created_at',
            'having' => 'users.id < 100',
            'orderby' => 'users.created_at desc',
        ]);

        factory(ReportSelect::class)->create([
            'report_id' => $this->report->id,
            'column' => 'users.id',
            'alias' => 'User ID'
        ]);

        factory(ReportJoin::class)->create([
            'report_id' => $this->report->id,
            'type' => 'left_join',
            'table' => 'posts',
            'first' => 'users.id',
            'operator' => '=',
            'second' => 'posts.user_id'
        ]);

        factory(ReportMiddleware::class)->create([
            'report_id' => $this->report->id
        ]);

        factory(ReportField::class)->create([
            'report_id' => $this->report->id,
            'label' => 'Created At',
            'type' => 'datetime',
            'model' => null,
            'alias' => 'users_created_date',
            'model_select_value' => null,
            'model_select_name' => null
        ]);

        $service = new BuildReportService($this->report, ['users_created_date' => '2019-01-01 00:00:00']);

        $this->assertEquals(
            'select users.id AS \'User ID\' from "users" left join "posts" on "users"."id" = "posts"."user_id" where users.created_at >= \'2019-01-01 00:00:00\' and users.id < 100 group by users.created_at having users.id < 100 order by users.created_at desc',
            $service->buildReportQuery()->toSql()
        );
    }

    /** @test **/
    public function can_get_data(): void
    {
        $report = factory(Report::class)->create();

        $service = new BuildReportService($report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'data');
        $method->setAccessible(true);

        $this->assertInstanceOf(LengthAwarePaginator::class, $method->invoke($service));
        $this->assertNotInstanceOf(Collection::class, $method->invoke($service));
    }

    /** @test **/
    public function getting_data_if_show_data_is_false_returns_false(): void
    {
        $report = factory(Report::class)->create([
            'show_data' => false
        ]);

        $service = new BuildReportService($report, []);

        $method = new \ReflectionMethod(BuildReportService::class, 'data');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($service));
    }

    /** @test **/
    public function getting_data_if_paginate_is_false_returns_full_collection(): void
    {
        $report = factory(Report::class)->create();

        $service = new BuildReportService($report, []);

        $service->paginate = false;

        $method = new \ReflectionMethod(BuildReportService::class, 'data');
        $method->setAccessible(true);

        $this->assertNotInstanceOf(LengthAwarePaginator::class, $method->invoke($service));
        $this->assertInstanceOf(Collection::class, $method->invoke($service));
    }

    /** @test */
    public function can_get_the_total_number_of_records_for_a_report(): void
    {
        $this->createFakeUser(5);

        $service = new BuildReportService($this->report, []);

        $this->assertEquals(5, $service->getTotalResults());
    }

}