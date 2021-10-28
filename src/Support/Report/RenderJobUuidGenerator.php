<?php

namespace MBLSolutions\Report\Support\Report;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RenderJobUuidGenerator
{

    public function __invoke(): Collection
    {
        return new Collection([
            'uuid' => $uuid = Str::uuid(),
            'href' => route('report.queue.job', ['job' => $uuid]),
        ]);

    }
    
}