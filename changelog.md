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