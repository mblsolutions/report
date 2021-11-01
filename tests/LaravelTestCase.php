<?php

namespace MBLSolutions\Report\Tests;

use Faker\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\ExcelServiceProvider;
use MBLSolutions\Report\Report;
use MBLSolutions\Report\ReportServiceProvider;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\Fakes\Middleware\FakeMiddleware;
use MBLSolutions\Report\Tests\Fakes\User;
use Orchestra\Testbench\TestCase as OTBTestCase;

class LaravelTestCase extends OTBTestCase
{
    use RefreshDatabase;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupEnvVariables();

        $this->runPackageMigrations();

        $this->loadTestMigrations();

        $this->loadModelFactories();

        Report::routes();
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $config = include __DIR__ . '/../config/report.php';

        $app['config']->set('report.models', $config['models']);
        $app['config']->set('report.data_types', $config['data_types']);
        $app['config']->set(
            'report.middleware', array_merge($config['middleware'], [FakeMiddleware::class])
        );
        $app['config']->set(
            'report.export_drivers', array_merge($config['export_drivers'], [FakeExportDriver::class])
        );
        $app['config']->set('report.filesystem', $config['filesystem']);
        $app['config']->set('report.filesystem_path', $config['filesystem_path']);

    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ReportServiceProvider::class,
            ExcelServiceProvider::class
        ];
    }

    /**
     * Run package migrations
     *
     * @return void
     */
    private function runPackageMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->artisan('migrate')->run();
    }

    /**
     * Load test migrations
     *
     * @return void
     */
    private function loadTestMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load model factories
     *
     * @return void
     */
    private function loadModelFactories(): void
    {
        $this->withFactories(__DIR__ . '/database/factories');
    }

    /**
     * Setup environment variables
     *
     * @return void
     */
    private function setupEnvVariables(): void
    {
        $this->app['config']->set('app.key', 'base64:KMRokGdMt+pgOmbRD+oiKwmfZiKAVxR6KkZ4KuiIo90=');
    }


    /**
     * Create Fake Users for testing
     *
     * @param int $count
     * @return Collection|User
     */
    protected function createFakeUser(int $count = 1)
    {
        if ($count > 1) {
            $users = collect([]);

            for ($i = 0; $i < $count; $i++) {
                $users->push($this->createUser());
            }

            return $users;
        }

        return $this->createUser();
    }

    /**
     * Create a Fake User
     *
     * @return User
     */
    private function createUser(): User
    {
        $user = new User(['name' => Str::uuid()]);

        $user->save();

        return $user->refresh();
    }

}