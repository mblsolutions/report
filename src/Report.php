<?php

namespace MBLSolutions\Report;

use Illuminate\Support\Facades\Route;

class Report
{

    /**
     * Binds the Report routes into the controller.
     *
     * @param  array  $options
     * @return void
     */
    public static function routes(array $options = []): void
    {
        static::manageRoutes($options);
        static::viewRoutes($options);
        static::exportRoutes($options);
    }

    /**
     * Register Report Manage Routes
     *
     * @param array $options
     * @return void
     */
    public static function manageRoutes(array $options = []): void
    {
        $defaultOptions = [
            'middleware' => 'api',
            'as' => 'report.',
            'namespace' => '\MBLSolutions\Report\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, static function () {

            Route::post('report/manage/{report}/test', 'ManageReportController@test')->name('manage.test');
            Route::get('report/manage/settings', 'ManageSettingsController@index')->name('manage.settings');

            Route::resource('report/manage', 'ManageReportController', [
                'only' => ['index', 'show', 'store', 'update', 'destroy'],
                'parameters' => [
                    'manage' => 'report'
                ]
            ]);

        });
    }

    /**
     * Register Report View Routes
     *
     * @param array $options
     * @return void
     */
    public static function viewRoutes(array $options = []): void
    {
        $defaultOptions = [
            'middleware' => 'api',
            'as' => 'report.',
            'namespace' => '\MBLSolutions\Report\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, static function () {
            Route::get('report/connection', 'ConnectionController@index')->name('connection.list');
            Route::get('report/middleware', 'ReportMiddlewareController@index')->name('middleware.list');
            Route::get('report/data/type', 'DataTypeController@index')->name('data.type.list');
            Route::get('report/model', 'ModelController@index')->name('model.list');

            // Scheduled Tasks
            Route::get('report/schedule/frequency', 'ScheduledReportController@frequencies')->name('schedule.frequencies');
            Route::get('report/schedule', 'ScheduledReportController@index')->name('schedule.index');
            Route::post('report/schedule', 'ScheduledReportController@create')->name('schedule.create');
            Route::delete('report/schedule/{schedule}', 'ScheduledReportController@destroy')->name('schedule.destroy');

            // Asynchronous
            Route::get('report/queue', 'QueuedReportController@index')->name('queue.index');
            Route::post('report/queue/{report}', 'QueuedReportController@render')->name('queue.render');
            Route::get('report/queue/job/{job}', 'QueuedReportController@job')->name('queue.job');
            Route::get('report/queue/export/{job}', 'QueuedReportController@export')->name('queue.export');

            // Synchronous
            Route::get('report', 'ReportController@index')->name('index');
            Route::get('report/{report}', 'ReportController@show')->name('show');
            Route::post('report/{report}', 'ReportController@render')->name('render');
            Route::post('report/{report}/preview', 'ReportController@preview')->name('preview');

            Route::post('report/{report}/export', 'ReportController@generateExport')->name('export');
        });
    }

    /**
     * Register Report Export/Print Routes
     *
     * @param array $options
     */
    public static function exportRoutes(array $options = []): void
    {
        $defaultOptions = [
            'middleware' => 'web',
            'as' => 'report.',
            'namespace' => '\MBLSolutions\Report\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, static function () {
            Route::get('report/{report}/export', 'ReportController@export')->name('export');
        });
    }

}