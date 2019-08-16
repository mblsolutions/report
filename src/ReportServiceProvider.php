<?php

namespace MBLSolutions\Report;

use Illuminate\Support\ServiceProvider;

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

        // Publish Report Components
        $this->publishes([
            __DIR__.'/../resources/js' => base_path('resources/js/report'),
        ], 'report-components');
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