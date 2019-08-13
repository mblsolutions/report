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
        \App\User::class
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
    | protection queries to reports to prevent users viewing data they
    | should not e.g. table.user_id = {current_user_id}
    |
    | @interface \MBLSolutions\Report\Interfaces\ReportMiddleware
    */

    'middleware' => [
        \MBLSolutions\Report\Middleware\Authenticated::class,
    ]

];
