<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ExportLinkLifeTest extends LaravelTestCase
{

    #[Test]
    public function export_links_last_three_days_by_default(): void
    {
        $config = include __DIR__ . '/../../config/report.php';

        $this->assertSame(3, (int) $config['link_expiration']);

        Carbon::setTestNow('2026-01-01 12:00:00');
        config(['report' => Arr::except(config('report'), 'link_expiration')]);

        $this->assertSame(Carbon::parse('2026-01-04 12:00:00')->timestamp, $this->linkExpiry());
    }

    #[Test]
    public function jobs_older_than_28_days_have_no_links(): void
    {
        $this->actingAsAdmin();

        $old = factory(ReportJob::class)->create(['created_at' => Carbon::now()->subDays(29)]);
        $recent = factory(ReportJob::class)->create(['created_at' => Carbon::now()->subDays(27)]);

        Storage::fake(config('report.filesystem'));

        $this->getJson(route('report.queue.export', ['job' => $old->getKey()]))->assertStatus(410);
        $this->getJson(route('report.queue.export', ['job' => $recent->getKey()]))->assertStatus(200);
    }

    private function linkExpiry(): int
    {
        $this->actingAsAdmin();

        $job = factory(ReportJob::class)->create();
        $disk = Storage::fake(config('report.filesystem', 'local'));
        $disk->put('reports/' . $job->getKey() . '/file.csv', 'a');
        $disk->buildTemporaryUrlsUsing(fn ($path, $expiration) => 'https://files.test/' . $path . '?expires=' . $expiration->timestamp);

        $url = $this->getJson(route('report.queue.export', ['job' => $job->getKey()]))->assertStatus(200)->json('urls.0.url');

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return (int) $query['expires'];
    }
}
