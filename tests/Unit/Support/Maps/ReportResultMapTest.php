<?php

namespace MBLSolutions\Report\Tests\Unit\Support\Maps;

use MBLSolutions\Report\DataType\CastString;
use MBLSolutions\Report\DataType\CastTitleCaseString;
use MBLSolutions\Report\Models\ReportSelect;
use MBLSolutions\Report\Support\Maps\ReportResultMap;
use MBLSolutions\Report\Tests\LaravelTestCase;
use ReflectionMethod;
use stdClass;

class ReportResultMapTest extends LaravelTestCase
{

    /** @test **/
    public function can_map_report_result_and_format(): void
    {
        $selects = collect([
            factory(ReportSelect::class)->create([
                'column' => 'name',
                'alias' => 'user_name',
                'type' => CastTitleCaseString::class,
                'column_order' => 0,
            ])
        ]);

        $attributes = new stdClass();

        $attributes->user_name = 'john doe';

        $map = new ReportResultMap($attributes);

        $this->assertEquals('John Doe', $map->format($selects)->user_name);
    }
    
    /** @test **/
    public function map_result_with_invalid_type_casts_data_as_string_by_default(): void
    {
        $method = new ReflectionMethod(ReportResultMap::class, 'getAttributeFormatter');
        $method->setAccessible(true);

        $formatter = $method->invoke(new ReportResultMap(new stdClass), 'not_valid');

        $this->assertInstanceOf(CastString::class, $formatter);
    }

}