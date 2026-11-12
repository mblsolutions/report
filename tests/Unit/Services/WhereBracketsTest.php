<?php

namespace MBLSolutions\Report\Tests\Unit\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class WhereBracketsTest extends LaravelTestCase
{

    #[Test]
    public function the_report_where_cannot_get_around_a_middleware_filter(): void
    {
        $this->createFakeUser(2);
        DB::table('users')->insert(['id' => 150, 'name' => 'other tenant']);

        $report = factory(Report::class)->create(['where' => 'users.id = 150 OR 1 = 1']);
        factory(ReportMiddleware::class)->create(['report_id' => $report->getKey()]);

        $query = (new BuildReportService($report, []))->buildReportQuery();

        $this->assertEquals('select * from "users" where (users.id = 150 OR 1 = 1) and users.id < 100', $query->toSql());
        $this->assertNotContains(150, $query->pluck('id')->map(fn ($id) => (int) $id)->all());
        $this->assertCount(2, $query->get());
    }

    #[Test]
    #[DataProvider('refusedWheres')]
    public function a_where_with_unbalanced_brackets_or_comments_is_refused(string $where): void
    {
        $report = factory(Report::class)->create(['where' => $where]);

        $this->expectException(InvalidArgumentException::class);

        (new BuildReportService($report, []))->buildReportQuery();
    }

    public static function refusedWheres(): array
    {
        return [
            'unclosed' => ['(users.id = 1'],
            'extra close' => ['users.id = 1)'],
            'closes early' => ['1=1) OR 1=1 OR (1=0'],
            'bracket in a comment' => ['1=1 /* ( */ ) OR (1=0 /* ) */'],
            'dash comment' => ['users.id = 1 -- x'],
            'hash comment' => ['users.id = 1 # x'],
            'block comment' => ['users.id = 1 /* x */'],
        ];
    }

    #[Test]
    #[DataProvider('allowedWheres')]
    public function brackets_and_comment_marks_inside_quotes_are_fine(string $where): void
    {
        $report = factory(Report::class)->create(['where' => $where]);

        $query = (new BuildReportService($report, []))->buildReportQuery();

        $this->assertEquals('select * from "users" where (' . $where . ')', $query->toSql());
    }

    public static function allowedWheres(): array
    {
        return [
            'bracket' => ["users.name = ')'"],
            'hash' => ["users.name = '#1'"],
            'dashes' => ["users.name = '--'"],
            'nested' => ["(users.id = 1 OR (users.name IN ('a', 'b'))) AND users.id > 0"],
        ];
    }
}
