<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MBLSolutions\Report\Support\HandlesAuthenticatable;

class ReportJobIndexResource extends JsonResource
{
    use HandlesAuthenticatable;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'authenticatable_id' => $this->getAttribute('authenticatable_id'),
            'authenticatable_name' => $this->getAttribute('authenticatable_name'),
            'created_at' => $this->getAttribute('created_at'),
            'deleted_at' => $this->getAttribute('deleted_at'),
            'exception' => $this->getAttribute('exception'),
            'formatted_parameters' => $this->getAttribute('formatted_parameters'),
            'parameters' => $this->getAttribute('parameters'),
            'processed' => $this->getAttribute('processed'),
            'query' => $this->when($this->authenticatableIsAdmin(), $this->getAttribute('query')),
            'report_id' => $this->getAttribute('report_id'),
            'report_name' => $this->getAttribute('report_name'),
            'schedule_id' => $this->getAttribute('schedule_id'),
            'status' => $this->getAttribute('status'),
            'total' => $this->getAttribute('total'),
            'updated_at' => $this->getAttribute('updated_at'),
            'uuid' => $this->getAttribute('uuid'),
        ];
    }

}
