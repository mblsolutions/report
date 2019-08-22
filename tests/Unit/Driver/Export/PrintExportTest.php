<?php

namespace MBLSolutions\Report\Tests\Unit\Driver\Export;

use Illuminate\View\View;
use MBLSolutions\Report\Driver\Export\PrintExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Tests\LaravelTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintExportTest extends LaravelTestCase
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
        $csv = new PrintExport();

        /** @var StreamedResponse $response */
        $response = $csv->export($this->report);

        $this->assertInstanceOf(View::class, $response);
    }

}