<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledReportResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @codeCoverageIgnore
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->getKey(),
            'schedule' => $this->getAttribute('schedule'),
            'report_id' => $this->getAttribute('report_id'),
            'authenticatable_id' => $this->getAttribute('authenticatable_id'),
            'parameters' => $this->getAttribute('parameters'),
            'last_run' => $this->getAttribute('last_run'),
            'created_at' => $this->getAttribute('created_at'),
            'updated_at' => $this->getAttribute('updated_at'),
        ];
    }

}
