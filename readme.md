# Laravel Report

[![Laravel Report Build](https://github.com/mblsolutions/report/actions/workflows/laravel-report-build-test.yml/badge.svg)](https://github.com/mblsolutions/report/actions/workflows/laravel-report-build-test.yml)

Import Laravel Reporting into any Laravel 6+ application using this package.

## Installation

Install Laravel Report with composer.

```bash
php composer require mblsolutions/report
```

Copy the package config to your local config.

```bash
php artisan vendor:publish --tag=report-config
```

Copy the package database migrations.

```bash
php artisan vendor:publish --tag=report-migrations
```

Laravel Report comes with its own database migrations, once the package has been installed run the
migrations.

```bash
php artisan migrate
``` 

## Upgrading to 6.0

6.0 changes who can run reports and adds limits. Read this before you update.

### Breaking changes

1. Inactive reports give 404 on every single-report route and don't run from a schedule or queue.
2. Admin checks need the user model to implement `IsAuthenticatableAnAdmin`. Without it, every user is a non-admin.
3. Non-admins can only run reports that carry a middleware listed in `tenant_middleware`. An empty list means no non-admin runs.
4. Runs whose saved user can't be loaded fail.
5. Signed export links need the signed-in user named in the link. Guests get 401, other users 403.
6. Reports run only on `allowed_connections` (default: the app's default connection).
7. MySQL per-query timeouts: 60 s on screen and direct exports, 300 s queued.
8. On-screen pages and direct CSV, JSON and Print exports stop at 50,000 rows (422; use the queued export).
9. More than 5 pending queued jobs per user gives 429.
10. Report editing (manage store, update, destroy and test) is off unless `management_enabled` is true.
11. Schedules: `report:schedule` does nothing until `schedules.enabled` is true. Create and update are admin-only. `recipients`, `frequency` and `parameters` are validated.
12. `render()` has no `raw` SQL for non-admins. Job details hide SQL and error text from non-admins.
13. Select field columns must be in `select_columns` (on save and for options).
14. Export file links last 3 days. Jobs older than 28 days give 410.
15. Direct CSV exports are quoted. In direct CSV and queued exports, text cells starting with `= + - @`, tab or CR get a leading `'`.
16. List `limit` is at most 100.
17. Queued report jobs have `$tries = 1`.
18. `replaceParameter()` with a value throws.
19. `UnauthorizedException` returns 403.

Apps that override a package controller, job or command don't get the changes made in that class.

### New config

The package doesn't merge its config into yours, so every new key has a default in code. Add the keys you want to change to your published `config/report.php`.

| Key | Env | Default |
| --- | --- | --- |
| `tenant_middleware` | - | `[]` (only admins can run reports) |
| `timeout.screen` / `timeout.queued` | `REPORT_TIMEOUT_SCREEN` / `REPORT_TIMEOUT_QUEUED` | 60 / 300 seconds per query |
| `max_rows.export` | `REPORT_MAX_EXPORT_ROWS` | 50,000 (queued exports have no limit) |
| `allowed_connections` | `REPORT_ALLOWED_CONNECTIONS` | the default connection only |
| `connection` | `REPORT_CONNECTION` | `null` |
| `max_pending_jobs` | `REPORT_MAX_PENDING_JOBS` | 5 |
| `management_enabled` | `REPORT_MANAGEMENT_ENABLED` | `false` |
| `select_columns` | - | `id, uuid, slug, sku, urn, name` |
| `schedules.enabled` | `REPORT_SCHEDULES` | `false` |
| `schedules.admin_only` | - | `true` |
| `schedules.recipient_domains` | `REPORT_RECIPIENT_DOMAINS` | `[]` (any domain) |
| `link_expiration` | `REPORT_LINK_EXPIRATION` | 3 days (was 6) |

`queued_export_drivers` is now also the list of drivers a queued run or schedule may use.

### Admins

Make your user model implement `IsAuthenticatableAnAdmin`:

```php
use MBLSolutions\Report\Interfaces\IsAuthenticatableAnAdmin;

class User extends Authenticatable implements IsAuthenticatableAnAdmin
{
    public function isAuthenticatableAnAdmin(): bool
    {
        return $this->is_admin;
    }
}
```

### Tenant middleware

A tenant middleware limits a report to the user's own rows. List it in `tenant_middleware` and add it to each report non-admins should run. For a user with no tenant, throw `UnauthorizedException` (403) instead of returning the query unchanged.

```php
use Illuminate\Database\Query\Builder;
use MBLSolutions\Report\Exceptions\UnauthorizedException;
use MBLSolutions\Report\Middleware\ReportMiddleware;

class UserAccount extends ReportMiddleware
{
    public function handle(Builder $builder): Builder
    {
        if ($this->authenticatable?->account_id === null) {
            throw new UnauthorizedException('You must belong to an account to view this report.');
        }

        return $builder->where('orders.account_id', '=', $this->authenticatable->account_id);
    }
}
```

```php
'tenant_middleware' => [
    \App\Report\Middleware\UserAccount::class,
],
```

A middleware's `field()` method can hide a report field from a user. The package then also skips the name lookup for that field's value.

## Usage

Once the package has been required into your project you can configure which parts of the package you 
would like to use.

### Routes

To enable the package json api routes in your application add the following to your routes file.

```php
Report::routes();
```

#### Manage routes

To configure custom middleware/guards around report management/creation only routes.

```php
Report::manageRoutes();
```

#### View Routes

To configure custom middleware/guards around report view only routes.

```php
Report::viewRoutes();
```

#### Export Routes

To configure custom middleware/guards around report export result only routes.

```php
Report::exportRoutes();
```

### Applying Custom Middleware/Gates

To apply middleware to the routes, wrap the routes in middleware groups.

```php
Route::middleware(['admin'])->group(function () {
    Report::manageRoutes();
});

Route::middleware(['user'])->group(function () {
    Report::viewRoutes();
});

Route::middleware(['web'])->group(function () {
    Report::exportRoutes();
});
```

To apply gates to the routes, wrap the routes in middleware groups.

```php
Route::middleware(['can:manage-reports'])->group(function () {
    Report::manageRoutes();
});

Route::middleware(['can:view-reports'])->group(function () {
    Report::viewRoutes();
});
```

### Report Select Parameter Models

To enable select options when creating/rendering reports, you must add the available model types to be reported on in the 
`report.php` config file. Any models added to this array will be available when creating new report fields.

Models added to the array should implement the `\MBLSolutions\Report\Interfaces\PopulatesReportOption` interface

Please Note: We recommend that large record sets are not used as select types, due to usability/browser performance issues.

```php
[
    'models' => [
        \App\User::class,
        \App\Order::class
    ]
]
```

### Scheduled Reporting

To enable scheduled reporting add the following line into the `schedule` method of the `\App\Console\Kernel` file of your
laravel application.

```php
$schedule->command(\MBLSolutions\Report\Console\Commands\DispatchScheduledReportsCommand::class)->hourly();
```

Schedules only run when `REPORT_SCHEDULES=true`.

### Report JSON API

The following endpoints are available to you once the routes have been added:

#### View Routes (synchronous)

| Method    | URI                           | Name                      |
| ---       | ---                           | ---                       |
| GET       | /api/report                   | report.index              |
| GET       | /api/report/{report}          | report.show               |
| POST      | /api/report/{report}          | report.render             |
| POST      | /report/{report}/export       | report.export             |
| GET       | /api/report/connection        | report.connection.list    |
| GET       | /api/report/middleware        | report.middleware.list    |
| GET       | /api/report/data/type         | report.data.type.list     |
| GET       | /api/report/model             | report.model.list         |


### Queued View Routes (asynchronous)

| Method    | URI                               | Name                          |
| ---       | ---                               | ---                           |
| GET       | /api/report/queue                 | report.queue.index            |
| GET       | /api/report/queue/pending         | report.queue.pending.index    |
| POST      | /api/report/queue/{report}        | report.queue.render           |
| GET       | /api/report/queue/job/{job}       | report.queue.job              |
| GET       | /api/report/queue/result/{job}    | report.queue.result           |
| GET       | /api/report/queue/export/{job}    | report.queue.export           |


### Scheduled Report Routes

| Method    | URI                               | Name                          |
| ---       | ---                               | ---                           |
| GET       | /report/schedule/frequency        | report.schedule.frequencies   |
| GET       | /report/schedule                  | report.schedule.index         |
| POST      | /report/schedule                  | report.schedule.create        |
| GET       | /report/schedule{schedule}        | report.schedule.show          |
| PATCH     | /report/schedule/{schedule}       | report.schedule.create        |
| DELETE    | /report/schedule/{schedule}       | report.schedule.destroy       |

#### Export Routes

| Method    | URI                           | Name                      |
| ---       | ---                           | ---                       |
| GET       | /report/{report}/export       | report.export             |

#### Manage Routes

| Method    | URI                           | Name                      |
| ---       | ---                           | ---                       |
| GET       | /api/report/manage            | report.manage.index       |
| POST      | /api/report/manage            | report.manage.store       |
| POST      | /api/report/test              | report.manage.test        |
| GET       | /api/report/manage/{report}   | report.manage.show        |
| PATCH     | /api/report/manage/{report}   | report.manage.update      |
| DELETE    | /api/report/manage/{report}   | report.manage.destroy     |
| DELETE    | /api/report/manage/settings   | report.manage.settings    |

### Report Events

Events are fired at critical points during report creation/completion

`ReportRendered` and `ReportExported` also carry `authenticatableId`, `parameters` (without the export link keys) and `rows`.

| Event                      | Description                                | Data                           | Namespace                                          |
| ---                        | ---                                        | ---                            | ---                                                |
| ReportCreated              | A new report was created.                  | Report $report                 |  MBLSolutions\Report\Events\ReportCreated          |
| ReportUpdated              | A report was updated.                      | Report $report                 |  MBLSolutions\Report\Events\ReportUpdated          |
| ReportDestroyed            | A report was deleted.                      | Report $report                 |  MBLSolutions\Report\Events\ReportDestroyed        |
| ReportRendered             | A report was rendered.                     | Report $report                 |  MBLSolutions\Report\Events\ReportRendered         |
| ReportExported             | A report was exported.                     | Report $report                 |  MBLSolutions\Report\Events\ReportExported         |
| ReportRenderStarted        | A queued report job to render was started. | Report $report, ReportJob $job |  MBLSolutions\Report\Events\ReportRenderStarted    |
| ReportChunkComplete        | A queued report job chunk was completed.   | Report $report, ReportJob $job |  MBLSolutions\Report\Events\ReportChunkComplete    |
| ReportRenderComplete       | A queued report job render was completed.  | Report $report, ReportJob $job |  MBLSolutions\Report\Events\ReportRenderComplete   |
| ScheduledReportDispatched  | A scheduled report was run.                | ScheduledReport $schedule      |  MBLSolutions\Report\Events\ReportRenderComplete   |