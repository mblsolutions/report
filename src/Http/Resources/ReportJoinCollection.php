<?php

namespace MBLSolutions\Report\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use MBLSolutions\Report\Models\ReportJoin;

class ReportJoinCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     * @codeCoverageIgnore
     */
    public function toArray($request): array
    {
        return $this->collection->transform(static function (ReportJoin $model) {
            return new ReportJoinResource($model);
        })->toArray();
    }
}
