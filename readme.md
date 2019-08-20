# MBL Solutions Report

Import MBL Solutions Reporting into any Laravel 5+ application using this package.

## Installation

Install MBL Solutions Report with composer.

```bash
php composer require mblsolutions/report
```

Copy the package config to your local config.

```bash
php artisan vendor:publish --tag=report-config
```

MBL Solutions comes with its own database migrations. Once the package has been installed run the
migrations.

```bash
php artisan migrate
``` 

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

### Front End Components

Once the packages api routing has been added to your project you have the option to create your own frontend 
that will interact with the api routes.

This package comes with pre-built `VueJS` components that you can use directly in your application.

To publish these assets, use the ```vendor:publish``` artisan command:

```bash
php artisan vendor:publish --tag=report-components
```

The published components will be placed the ```resources/js/report``` directory. Once the components have been 
published, you should register them in your ```reousrces/js/app.js``` file.

```javascript
const app = new Vue({
    el: '#app',
    components: {
        // MBL Solutions Report Package components
        'mbl-manage-report': require('./report/components/ManageReport').default,
        'mbl-show-report': require('./report/components/ShowReport').default
    }
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

### Report JSON API

The following endpoints are available to you once the routes have been added:

#### View Routes

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

