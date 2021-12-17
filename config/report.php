<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report Selected Models
    |--------------------------------------------------------------------------
    |
    | Available Models that can be used for Select type criteria
    | e.g. User Model \App\User::class
    |
    | @interface \MBLSolutions\Report\Interfaces\PopulatesReportOption
    */

    'models' => [
        \App\Models\User::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Data Types
    |--------------------------------------------------------------------------
    |
    | Data types are used to cast result data to a certain format/style
    |
    | @interface \MBLSolutions\Report\Interfaces\ReportDataType
    */

    'data_types' => [
        \MBLSolutions\Report\DataType\CastString::class,
        \MBLSolutions\Report\DataType\CastUppercaseString::class,
        \MBLSolutions\Report\DataType\CastTitleCaseString::class,
        \MBLSolutions\Report\DataType\CastInteger::class,
        \MBLSolutions\Report\DataType\CastDecimal::class,
        \MBLSolutions\Report\DataType\CastDate::class,
        \MBLSolutions\Report\DataType\CastTime::class,
        \MBLSolutions\Report\DataType\CastDateTime::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Middleware
    |--------------------------------------------------------------------------
    |
    | Reports can be hidden and/or protected using custom middleware to
    | prevent users viewing reports. Middleware can also be used to add
    | protection queries to report's preventing users viewing data they
    | should not e.g. table.user_id = {current_user_id}
    |
    | @interface \MBLSolutions\Report\Interfaces\ReportMiddleware
    */

    'middleware' => [
        \MBLSolutions\Report\Middleware\Authenticated::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Authenticatable Model
    |--------------------------------------------------------------------------
    |
    | The authenticatable model (normally User) injected into middleware
    */

    'authenticatable_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Export Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the export drivers available for export report
    | results out of the application.
    |
    | Drivers: "csv", "print", "json"
    |
    | @interface \MBLSolutions\Report\Interfaces\ExportDriver
    */

    'export_drivers' => [
        \MBLSolutions\Report\Driver\Export\CsvExport::class,
        \MBLSolutions\Report\Driver\Export\PrintExport::class,
        \MBLSolutions\Report\Driver\Export\JsonExport::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Specify the filesystem driver you would like to use for reports to be
    | stored.
    |
    */

    'filesystem' => env('REPORT_FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Report Filesystem Path
    |--------------------------------------------------------------------------
    |
    | Here you may configure the path on the filesystem disk where reports should
    | be stored.
    |
    */

    'filesystem_path' => env('REPORT_FILESYSTEM_PATH', 'reports/'),

    /*
    |--------------------------------------------------------------------------
    | Report Chunk Limit
    |--------------------------------------------------------------------------
    |
    | Here you may configure the report export chunk limit, each file of results
    | will be limited to this number
    |
    */

    'chunk_limit' => env('REPORT_CHUNK_LIMIT', 50000),

    /*
    |--------------------------------------------------------------------------
    | Queued Export Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the export drivers available for queued report
    | results out of the application.
    |
    | Drivers: "csv", "xls", "xlsx", "ods", "tsv"
    |
    | @interface \MBLSolutions\Report\Interfaces\ExportDriver
    */

    'queued_export_drivers' => [
        \MBLSolutions\Report\Driver\QueuedExport\CsvQueuedExport::class,
        \MBLSolutions\Report\Driver\QueuedExport\XlsQueuedExport::class,
        \MBLSolutions\Report\Driver\QueuedExport\XlsxQueuedExport::class,
        \MBLSolutions\Report\Driver\QueuedExport\OdsQueuedExport::class,
        \MBLSolutions\Report\Driver\QueuedExport\TsvQueuedExport::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Preview Limit
    |--------------------------------------------------------------------------
    |
    | The maximum number of records that should be displayed as part of the
    | report preview.
    |
    */

    'preview_limit' => env('REPORT_PREVIEW_LIMIT', 1000),

    /*
    |--------------------------------------------------------------------------
    | Scheduled Report Date Fields
    |--------------------------------------------------------------------------
    |
    | Any date/time parameters that should be replaced when running a scheduled
    | report e.g. 'start_date' would be replaced with 2021-01-01 00:00:00 and
    | 'end_date' would be replaced with 2021-12-31 23:59:59
    |
    */

    'scheduled_date_start' => [
        'start_date',
    ],

    'scheduled_date_end' => [
        'end_date',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Link Expiration
    |--------------------------------------------------------------------------
    |
    | The length of time that an export link may remain valid in days.
    |
    */

    'link_expiration' => env('REPORT_LINK_EXPIRATION', 32),

];