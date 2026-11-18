<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use MBLSolutions\Report\DataType\CastString;
use MBLSolutions\Report\Driver\Export\CsvExport;
use MBLSolutions\Report\Driver\Export\JsonExport;
use MBLSolutions\Report\Export\Report\ReportExport;
use MBLSolutions\Report\Export\Sheets\ReportDataSheet;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvCellsTest extends LaravelTestCase
{
    private const FORMULA = '=HYPERLINK("http://example.test","x")';

    private const AWKWARD = "a, \"b\"\nc";

    protected Report $report;

    protected User $admin;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createFakeUser();
        $this->admin->setIsAdmin();
        $this->admin->update(['name' => 'admin']);

        foreach ([self::FORMULA, '-5', self::AWKWARD, '@SUM(A1)'] as $name) {
            (new User(['name' => $name]))->save();
        }

        $this->report = factory(Report::class)->create();
        factory(ReportSelect::class)->create([
            'report_id' => $this->report->getKey(),
            'column' => 'users.name',
            'alias' => 'name',
            'type' => CastString::class,
        ]);

        $this->actingAs($this->admin);
    }

    #[Test]
    public function the_direct_csv_is_quoted_and_guards_formula_cells(): void
    {
        $link = $this->report->getExportLink(new Request([
            'driver' => ReportExportDrivers::hashDriverValue(CsvExport::class),
        ]));

        $csv = $this->get($link)->assertStatus(200)->streamedContent();

        $rows = [];
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csv);
        rewind($stream);
        while (($row = fgetcsv($stream, null, ',', '"', '')) !== false) {
            $rows[] = $row[0];
        }

        $this->assertSame('name', $rows[0]);
        $this->assertEqualsCanonicalizing(
            ["'" . self::FORMULA, '-5', self::AWKWARD, "'@SUM(A1)", 'admin'],
            array_slice($rows, 1)
        );
    }

    #[Test]
    public function queued_sheets_guard_formula_cells(): void
    {
        $service = new BuildReportService($this->report, [], false);

        foreach ([new ReportExport($service, 0, 10), new ReportDataSheet($service, 0, 10)] as $export) {
            $names = $export->collection()->pluck('name')->all();

            $this->assertContains("'" . self::FORMULA, $names);
            $this->assertContains('-5', $names);
            $this->assertNotContains(self::FORMULA, $names);
        }
    }

    #[Test]
    public function a_missing_format_does_not_error(): void
    {
        $this->assertStringStartsWith('[{"report"', $this->content((new JsonExport())->export($this->report)));
        $this->assertStringStartsWith('name', $this->content((new CsvExport())->export($this->report)));
    }

    private function content(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }
}
