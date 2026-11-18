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
    | Report Authenticatable Name
    |--------------------------------------------------------------------------
    |
    | The authenticatable model (normally User) name attribute (default name)
    */

    'authenticatable_name' => 'name',

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
    | Preview Dataset Limit
    |--------------------------------------------------------------------------
    |
    | Here you may configure the maximum number of results to be used before a GROUP BY
    | takes place. This is only used for Report preview, not in the final CSV export.
    |
    */

    'preview_results_limit' => env('REPORT_PREVIEW_RESULTS_LIMIT', 15000),

    /*
    |--------------------------------------------------------------------------
    | Include CSV Download Links in Emails
    |--------------------------------------------------------------------------
    |
    | Here you may configure if the report email should contain download links
    | of the generated CSV files
    |
    */

    'csv_links_in_emails' => env('REPORT_CSV_LINKS_IN_EMAIL', false),

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
    | Jobs older than 28 days have no links.
    |
    */

    'link_expiration' => env('REPORT_LINK_EXPIRATION', 3),

    /*
    |--------------------------------------------------------------------------
    | Tenant Middleware
    |--------------------------------------------------------------------------
    |
    | Report middleware that limits rows to the user's own data. Non-admins can
    | only run reports that carry one of these. An empty list means only admins
    | can run reports. Admins are users whose model implements
    | \MBLSolutions\Report\Interfaces\IsAuthenticatableAnAdmin.
    |
    */

    'tenant_middleware' => [
        // \App\Report\Middleware\UserAccount::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Timeouts
    |--------------------------------------------------------------------------
    |
    | Seconds each report query may run on a MySQL connection: on screen and
    | direct exports, and in queued jobs. Reports run on a copy of the
    | connection, so the app's own queries are not affected.
    |
    */

    'timeout' => [
        'screen' => env('REPORT_TIMEOUT_SCREEN', 60),
        'queued' => env('REPORT_TIMEOUT_QUEUED', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Row Limits
    |--------------------------------------------------------------------------
    |
    | The most rows an on-screen page or a direct CSV, JSON or Print export may
    | reach. Larger reports must use the queued export, which has no limit.
    |
    */

    'max_rows' => [
        'export' => env('REPORT_MAX_EXPORT_ROWS', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Connections
    |--------------------------------------------------------------------------
    |
    | Connections reports may use (comma separated). Empty means the default
    | connection only. If "connection" is set, every report runs on it.
    |
    */

    'allowed_connections' => array_filter(explode(',', env('REPORT_ALLOWED_CONNECTIONS', ''))),

    'connection' => env('REPORT_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Pending Queued Reports
    |--------------------------------------------------------------------------
    |
    | How many queued reports a user may have waiting or running (from the
    | last hour) before new ones are refused with 429.
    |
    */

    'max_pending_jobs' => env('REPORT_MAX_PENDING_JOBS', 5),

    /*
    |--------------------------------------------------------------------------
    | Report Editing
    |--------------------------------------------------------------------------
    |
    | Creating, editing, deleting and testing reports is off unless this is
    | true. Listing and showing reports still work.
    |
    */

    'management_enabled' => env('REPORT_MANAGEMENT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Select Field Columns
    |--------------------------------------------------------------------------
    |
    | Columns a select field may use for its value and name.
    |
    */

    'select_columns' => ['id', 'uuid', 'slug', 'sku', 'urn', 'name'],

    /*
    |--------------------------------------------------------------------------
    | Scheduled Reports
    |--------------------------------------------------------------------------
    |
    | "enabled": report:schedule only runs schedules when this is true.
    | "admin_only": only admins can create and edit schedules. Owners can
    | still delete their own.
    | "recipient_domains": email domains recipients must use (comma
    | separated). Empty allows any domain.
    |
    */

    'schedules' => [
        'enabled' => env('REPORT_SCHEDULES', false),
        'admin_only' => true,
        'recipient_domains' => array_filter(explode(',', env('REPORT_RECIPIENT_DOMAINS', ''))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Load Migrations
    |--------------------------------------------------------------------------
    |
    | This option controls whether the package will automatically load its
    | own database migrations from its 'database/migrations' directory.
    |
    | When set to true, the package's migrations will be loaded and executed
    | during migration commands (e.g., when you run 'php artisan migrate').
    | This is the default behavior, allowing the package to manage its
    | database schema requirements seamlessly.
    |
    | When set to false, the package will not load its own migrations. This
    | is useful in scenarios where you have published the package's migrations
    | to your application's 'database/migrations' directory—perhaps to modify
    | them or to prevent conflicts with existing tables. By disabling the
    | automatic loading of the package's migrations, you gain full control
    | over when and how these migrations are executed.
    |
    | Use Cases:
    | - **Customizing Migrations:** If you've published and modified the
    |   package's migration files to suit your application's needs.
    | - **Avoiding Conflicts:** If the package's migrations create tables or
    |   modify schemas that already exist in your database, disabling automatic
    |   loading prevents errors and data loss.
    | - **Renaming Migrations:** If you've renamed the migration files after
    |   publishing them to avoid filename collisions or to enforce a specific
    |   migration order.
    |
    | **Note:** After setting this option to false, ensure that you've published
    | the migrations using the package's publish command:
    |   'php artisan vendor:publish --tag=report-config'
    | and that they are placed in your application's 'database/migrations' directory.
    |
    | Default: true
    |
    */
    'load_migrations' => true,

];