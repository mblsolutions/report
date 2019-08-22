<?php

namespace MBLSolutions\Report\Tests\Unit\Driver\Export;

use MBLSolutions\Report\Driver\Export\CsvExport;
use MBLSolutions\Report\Driver\Export\JsonExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Tests\LaravelTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonExportTest extends LaravelTestCase
{
    /** @var Report $report */
    protected $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->report = factory(Report::class)->create();
    }

    /** @test **/
    public function can_export(): void
    {
        $csv = new JsonExport();

        /** @var StreamedResponse $response */
        $response = $csv->export($this->report);

        $this->assertTrue($response->isSuccessful());
    }

}