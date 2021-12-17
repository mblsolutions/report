<?php

namespace MBLSolutions\Report\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use MBLSolutions\Report\Models\ReportJob;
use RuntimeException;

class QueuedReportExportService
{
    protected ReportJob $job;

    public function __construct(ReportJob $job)
    {
        $this->job = $job;
    }

    public function getExportResponse(): Collection
    {
        return new Collection([
            'name' => $this->job->report->getAttribute('name'),
            'date' => $this->job->getAttribute('created_at'),
            'uuid' => $this->job->getKey(),
            'total' => $this->job->getAttribute('total'),
            'chunk_limit' => config('report.chunk_limit', 50000),
            'urls' => $this->getExportLinks()->toArray()
        ]);
    }

    /**
     * Get Export Links
     *
     * @return Collection
     */
    public function getExportLinks(): Collection
    {
        $links = new Collection([]);

        foreach ($this->getExportFiles() as $file) {
            $links->push($this->createExportLink($file));
        }

        return $links;
    }

    /**
     * Create export link
     *
     * @param string $path
     * @return array
     */
    protected function createExportLink(string $path): array
    {
        $name = $this->getFileName($path);

        try {
            $url = Storage::disk(config('report.filesystem'))->temporaryUrl($path, Carbon::now()->addDays(config('report.link_expiration', 32)),
                [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => 'attachment; filename=' . $name,
                ]
            );
        } catch (RuntimeException $exception) {
            $url = Storage::disk(config('report.filesystem'))->temporaryUrl($path, Carbon::now()->addDays(config('report.link_expiration', 32)),
                [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => 'attachment; filename=' . $name,
                ]
            );
        }

        return [
            'name' => $name,
            'url' => $url
        ];
    }

    /**
     * Get the filename from the Path
     *
     * @param string $path
     * @return false|string
     */
    protected function getFileName(string $path)
    {
        return substr(strrchr($path, '/'), 1);
    }

    /**
     * Get export Files
     *
     * @return array
     */
    protected function getExportFiles(): array
    {
        $path = config('report.filesystem_path', 'reports/') . $this->job->getKey();

        return Storage::disk(config('report.filesystem'))->allFiles($path);
    }

}