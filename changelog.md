## v2.7.7

+ AWS temp URLs are only available upto 7 days

## v2.7.6

+ Update the expiration time on queued report export links

## v2.7.5

+ Sort selectable report parameters by the report model name
+ Inactive reports showing when using all on ReportRepository
+ Allow Enums to be selectable report parameters 

## v2.7.4

+ Add new pending queued report job index route
+ Add resources to queued report job routes

## v2.7.3

+ Allow 0 values on exports using `WithStrictNullComparison`

## v2.7.2

+ Update default User model namespace based on framework defaults
+ Update Authenticated report middleware to use $this->authenticatable (for queued reports) 

## v2.7.1

+ Bug fix, formatted parameter dates are being set as current date
+ Bug fix, file name changes due to the chunk totalling introduced in v2.7.0

## v2.7.0

+ Update report total calculations to account for SQL group bys
+ Report Totals are now calculated on a chunk by chunk basis to avoid large reports timing out when calculating reports 
  with large many results (> 100k)

## v2.6.4

+ Add parameters to supported export types (xls, xlsx and ods)
+ Fixed an issue where schedule ids were being incorrectly saved to report_jobs

## v2.6.3

+ Inject authenticatable into Report Render Job
+ Inject authenticatable into fields middleware to toggle display of hidden fields

## v2.6.2

+ Updated middleware to allow injection of Authenticated model when running reports on a queue

## v2.6.1

+ Added License

## v2.6.0

+ Remove hourly from report schedule due to complexities on time based replacements (to be added back in once time permits)
+ Apply date filtering to scheduled reports 
  + e.g. Today 2021-01-01 00:00:00 (daily report would replace parameters start_date 2020-12-31 00:00:00 end_date 2020-12-31 23:59:59)

## v2.5.0

+ Add report schedule show and update routes
  + GET /report/schedule/{schedule}
  + PATCH /report/schedule/{schedule}
+ Add formatted parameters to report jobs table
+ Update report builder service parameters with type hinting

## v2.4.1

+ Loosen schedule validation to rush within the first minute (because CRONs are never on time it seems).

## v2.4.0

+ Add scheduled report migration
+ Add scheduled report model
+ Add scheduled report routes
  + GET /report/schedule
  + POST /report/schedule
  + DELETE /report/schedule/{schedule}
+ Add new Command `\MBLSolutions\Report\Console\Commands\DispatchScheduledReportsCommand::class`
+ Add a report render preview, limited to 1000 results (can be overridden use the `REPORT_PREVIEW_LIMIT` env)

## v2.3.0

+ Fire event for each job chunk completed
+ Add support for queued export in multiple formats using new export interface (\MBLSolutions\Report\Interfaces\ExportDriver)

## v2.2.0

+ Fix report export render to build report
+ Add headings to report export

## v2.1.0

+ Chunked export on Queued jobs
+ Update queued export report to handle multiple urls

## v2.0.2

+ Add support for queued report rendering
  + GET /api/report/queue              report.queue.index
  + POST /api/report/queue/{report}    report.queue.render
  + GET /api/report/queue/job/{job}    report.queue.job
  + GET /api/report/queue/result/{job} report.queue.result
  + GET /api/report/queue/export/{job} report.queue.export
+ Persist report data in to filesystem as a CSV
+ Export report from filesystem

## v2.0.1

+ Remove support for Laravel version 5
+ Require illuminate/queue

## v2.0.0

+ bump support for php from ^7.1 to 7.4 or ^8.0

## v1.3.0

+ Regex OR modified 

## v1.1.1

+ Remove type hinting from resource collections (higher versions of laravel break resource 
  collection compatibility)

## v1.1.0

+ Update workflow and composer dependencies

## v1.0.8

+ add action status badge to readme

## v1.0.7

+ bump support for laravel 7 and 8
+ add GitHub actions workflow
+ remove circle ci

## v1.0.6

+ account for time addition to where replacements

## v1.0.5

+ remove debug statements

## v1.0.4

+ pass options to new interface

## v1.0.3

+ set default interface options

## v1.0.0

+ Initial Release
