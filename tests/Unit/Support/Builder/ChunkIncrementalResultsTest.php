<?php

namespace MBLSolutions\Report\Tests\Unit\Support\Builder;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Support\Builder\ChunkIncrementalResults;
use MBLSolutions\Report\Tests\LaravelTestCase;
use ReflectionClass;

class ChunkIncrementalResultsTest extends LaravelTestCase
{
    protected ChunkIncrementalResults $helper;

    protected Builder $builder;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        factory(Report::class, 12)->create();

        $this->builder = $this->createTestBuilder();

        $this->helper = new ChunkIncrementalResults($this->builder, 5);
    }

    /** @test **/
    public function can_create_object(): void
    {
        $this->assertInstanceOf(ChunkIncrementalResults::class, $this->helper);
    }

    /** @test **/
    public function can_get_a_cleaned_query_for_accurate_result_counting(): void
    {
        $reflection = new ReflectionClass(ChunkIncrementalResults::class);

        $method = $reflection->getMethod('cleanBuilderForResultCount');
        $method->setAccessible(true);

        $this->assertEquals('select * from "reports"', $method->invoke($this->helper)->toSql());
    }

    /** @test **/
    public function can_get_the_count_limit(): void
    {
        $reflection = new ReflectionClass(ChunkIncrementalResults::class);

        $method = $reflection->getMethod('getLimit');
        $method->setAccessible(true);

        $helper = new ChunkIncrementalResults($this->builder, 50000);

        $this->assertEquals(50001, $method->invoke($helper));
    }

    /** @test **/
    public function can_get_the_total_results_for_a_chunk(): void
    {
        $this->assertEquals(5, $this->helper->getTotalResultsForChunk(0));
    }

    /** @test **/
    public function can_get_the_total_results_for_a_chunk_with_less_than_chunk_limit(): void
    {
        $helper = new ChunkIncrementalResults($this->builder, 100);

        $this->assertEquals(12, $helper->getTotalResultsForChunk(0));
    }
    
    /** @test **/
    public function can_check_if_there_are_more_results(): void
    {
        $this->assertTrue($this->helper->hasMoreResults(0));
    }
    
    /** @test **/
    public function can_check_if_there_are_more_results_on_last_chunk(): void
    {
        $helper = new ChunkIncrementalResults($this->builder, 4);

        $this->assertFalse($helper->hasMoreResults(8));
    }

    protected function createTestBuilder(): Builder
    {
        return DB::table('reports')
                   ->selectRaw('reports.name AS report_name')
                   ->selectRaw(' reports.connection AS report_connection')
                   ->limit(3)
                   ->offset(0)
                   ->orderBy('reports.name');
    }

}