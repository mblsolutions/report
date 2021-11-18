## v2.6.1
+ Added Software License

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
