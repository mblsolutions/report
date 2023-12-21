<?php

/* @var $factory Factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportFieldType;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Models\ScheduledReport;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Support\Enums\JobStatus;
use MBLSolutions\Report\Support\Enums\ReportSchedule;
use MBLSolutions\Report\Tests\Fakes\Middleware\FakeMiddleware;

$factory->define(Report::class, static function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'description' => $faker->sentence(),
        'connection' => 'testing',
        'display_limit' => 25,
        'table' => 'users',
        'where' => null,
        'groupby' => null,
        'having' => null,
        'orderby' => null,
        'show_data' => true,
        'show_totals' => false,
        'active' => true
    ];
});

$factory->define(ReportSelect::class, static function (Faker $faker) {
    return [
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'column' => 'name',
        'alias' => 'user_name',
        'type' => CastTitleCaseString::class,
        'column_order' => 0,
    ];
});

$factory->define(ReportJoin::class, static function (Faker $faker) {
    return [
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'type' => 'left_join',
        'table' => 'posts',
        'first' => 'users.id',
        'operator' => '=',
        'second' => 'posts.user_id'
    ];
});

$factory->define(ReportMiddleware::class, static function (Faker $faker) {
    return array(
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'middleware' => FakeMiddleware::class
    );
});

$factory->define(ReportField::class, static function (Faker $faker) {
    return [
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'label' => 'Created At',
        'type' => ReportFieldType::DATETIME,
        'model' => null,
        'alias' => 'users_created_date',
        'model_select_value' => null,
        'model_select_name' => null
    ];
});

$factory->state(ReportField::class, 'time', static function (Faker $faker) {
    return [
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'label' => 'Start Time',
        'type' => ReportFieldType::TIME,
        'model' => null,
        'alias' => 'start_time',
        'model_select_value' => null,
        'model_select_name' => null
    ];
});

$factory->state(ReportField::class, 'date', static function (Faker $faker) {
    return [
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'label' => 'Start Date',
        'type' => ReportFieldType::DATE,
        'model' => null,
        'alias' => 'start_date',
        'model_select_value' => null,
        'model_select_name' => null
    ];
});

$factory->state(ReportField::class, 'datetime', static function (Faker $faker) {
    return [
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'label' => 'Start DateTime',
        'type' => ReportFieldType::DATETIME,
        'model' => null,
        'alias' => 'start_date',
        'model_select_value' => null,
        'model_select_name' => null
    ];
});

$factory->define(ReportJob::class, static function (Faker $faker) {
    return [
        'uuid' => Str::uuid(),
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'status' => JobStatus::SCHEDULED,
        'authenticatable_id' => null,
        'schedule_id' => null,
    ];
});

$factory->define(ScheduledReport::class, static function (Faker $faker) {
    return [
        'uuid' => Str::uuid(),
        'report_id' => function () {
            return factory(Report::class)->create();
        },
        'parameters' => [
            'export_driver' => CsvQueuedExport::class
        ],
        'frequency' => ReportSchedule::MONTHLY,
        'limit' => null,
        'recipients' => null,
        'last_run' => null,
        'authenticatable_id' => null,
    ];
});