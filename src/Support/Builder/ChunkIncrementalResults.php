<?php

namespace MBLSolutions\Report\Support\Builder;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class ChunkIncrementalResults
{
    protected int $limit;

    protected int $plusCount;

    protected Builder $builder;

    protected ?Collection $result = null;

    /**
     * Helper to Chunk Results Incrementally
     */
    public function __construct(Builder $builder, int $limit, int $plusCount = 1)
    {
        $this->builder = $builder;
        $this->limit = $limit;
        $this->plusCount = $plusCount;
    }

    /**
     * Get the total Results for the current chunk
     *
     * @param int $offset
     * @param string|array|null $columns defaults to "select 1"
     * @return int
     */
    public function getTotalResultsForChunk(int $offset, $columns = null): int
    {
        $builder = $this->cleanBuilderForResultCount();

        $this->result = $builder->limit($this->getLimit())->offset($offset)->get($columns ?? [new Expression('1')]);

        $count = $this->result->count();

        return $count <= $this->limit ? $count : ($count - $this->plusCount);
    }

    /**
     * Determine if the builder has more results
     *
     * @param int $offset
     * @param string|array|null $columns
     * @return bool
     */
    public function hasMoreResults(int $offset, $columns = null): bool
    {
        if ($this->result === null) {
            $this->getTotalResultsForChunk($offset, $columns);
        }

        return $this->result->count() > ($this->limit);
    }

    /**
     * Clean Builder for accurate Result counting
     *
     * @param string|array $without
     * @return Builder
     */
    protected function cleanBuilderForResultCount(array $without = ['columns', 'orders', 'limit', 'offset']): Builder
    {
        return $this->builder->cloneWithout($without);
    }

    /**
     * Get the limit
     *
     * @return int
     */
    protected function getLimit(): int
    {
        return ($this->limit + $this->plusCount);
    }

}