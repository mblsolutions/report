<?php

namespace MBLSolutions\Report;

use Illuminate\Support\ServiceProvider;
use MBLSolutions\Report\Console\Commands\DispatchScheduledReportsCommand;

class ReportServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mbl-report');

        // Publish MBL Solutions report config
        $this->publishes([
            __DIR__ . '/../config/report.php' => config_path('report.php'),
        ], 'report-config');

        // Publish MBL Solutions report database migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'report-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DispatchScheduledReportsCommand::class
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

}