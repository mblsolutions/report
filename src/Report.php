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
            Route::resource('report/manage', 'ManageReportController', [
                'only' => ['index', 'show', 'store', 'update', 'destroy'],
                'parameters' => [
                    'manage' => 'report'
                ]
            ]);
            Route::get('report/model', 'ModelController@index')->name('model.list');
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
            Route::get('report', 'ReportController@index')->name('index');
            Route::post('report/{report}', 'ReportController@show')->name('show');
        });
    }

}