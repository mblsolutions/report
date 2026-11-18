<?php

namespace MBLSolutions\Report\Tests\Unit\Driver\Export;

use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\DataType\CastString;
use MBLSolutions\Report\Driver\Export\JsonExport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonExportTest extends LaravelTestCase
{
    /** @var Report $report */
    protected $report;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsAdmin();

        $this->report = factory(Report::class)->create();
    }

    #[Test]
    public function can_export(): void
    {
        $csv = new JsonExport();

        /** @var StreamedResponse $response */
        $response = $csv->export($this->report);

        $this->assertTrue($response->isSuccessful());
    }

    #[Test]
    public function an_export_with_rows_is_valid_json(): void
    {
        $this->addUsers(3);

        $this->assertSame(['user 1', 'user 2', 'user 3'], $this->exportedNames());
    }

    #[Test]
    public function an_export_over_several_chunks_is_valid_json(): void
    {
        // Two full chunks of 100, so the last fetch is empty
        $this->addUsers(200);

        $names = $this->exportedNames();

        $this->assertCount(200, $names);
        $this->assertSame('user 1', $names[0]);
    }

    #[Test]
    public function an_empty_export_is_valid_json(): void
    {
        $this->assertSame([], $this->exportedNames());
    }

    private function addUsers(int $count): void
    {
        DB::table('users')->insert(array_map(
            static fn (int $i) => ['name' => sprintf('user %d', $i)],
            range(1, $count)
        ));
    }

    /**
     * Export the users' names and return them in file order
     */
    private function exportedNames(): array
    {
        factory(ReportSelect::class)->create([
            'report_id' => $this->report->getKey(),
            'column' => 'users.id',
            'alias' => 'id',
            'type' => CastString::class,
        ]);
        factory(ReportSelect::class)->create([
            'report_id' => $this->report->getKey(),
            'column' => 'users.name',
            'alias' => 'name',
            'type' => CastString::class,
        ]);

        ob_start();
        (new JsonExport())->export($this->report)->sendContent();
        $json = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($this->report->name, $json[0]['report']['name']);

        return array_column(array_merge(...array_slice($json, 1)), 'name');
    }
}
