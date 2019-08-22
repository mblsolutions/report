<?php

/* @var $factory Factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportJoin;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Tests\Fakes\Middleware\FakeMiddleware;

$factory->define(Report::class, static function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'description' => $faker->sentence(),
        'connection' => 'sqlite',
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
        'report_id' => factory(Report::class)->create(),
        'column' => 'name',
        'alias' => 'user_name',
        'type' => CastTitleCaseString::class,
        'column_order' => 0,
    ];
});

$factory->define(ReportJoin::class, static function (Faker $faker) {
    return [
        'report_id' => factory(Report::class)->create(),
        'type' => 'left_join',
        'table' => 'posts',
        'first' => 'users.id',
        'operator' => '=',
        'second' => 'posts.user_id'
    ];
});

$factory->define(ReportMiddleware::class, static function (Faker $faker) {
    return array(
        'report_id' => factory(Report::class)->create(),
        'middleware' => FakeMiddleware::class
    );
});

$factory->define(ReportField::class, static function (Faker $faker) {
    return [
        'report_id' => factory(Report::class)->create(),
        'label' => 'Created At',
        'type' => 'datetime',
        'model' => null,
        'alias' => 'users_created_date',
        'model_select_value' => null,
        'model_select_name' => null
    ];
});