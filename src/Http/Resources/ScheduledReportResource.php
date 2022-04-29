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
            'report_name' => $this->getAttribute('report_name'),
            'report_description' => $this->getAttribute('report_description'),
            'frequency' => $this->getAttribute('frequency'),
            'report_id' => $this->getAttribute('report_id'),
            'authenticatable_id' => $this->getAttribute('authenticatable_id'),
            'authenticatable_name' => $this->getAttribute('authenticatable_name'),
            'parameters' => $this->getAttribute('parameters'),
            'recipients' => $this->getAttribute('recipients'),
            'last_run' => $this->getAttribute('last_run'),
            'created_at' => $this->getAttribute('created_at'),
            'updated_at' => $this->getAttribute('updated_at'),
        ];
    }

}
