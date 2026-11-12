<?php

namespace MBLSolutions\Report\Tests\Unit\Services;

use InvalidArgumentException;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReplaceParameterTest extends LaravelTestCase
{

    #[Test]
    public function replace_parameter_refuses_a_value(): void
    {
        $service = new BuildReportService(factory(Report::class)->create(), []);

        $this->expectException(InvalidArgumentException::class);

        $service->replaceParameter('name', "x' OR '1'='1", "users.name = '{name}'");
    }

    #[Test]
    public function an_alias_with_regex_characters_is_removed(): void
    {
        $service = new BuildReportService(factory(Report::class)->create(), []);

        $this->assertSame(
            'users.id = 1',
            trim($service->replaceParameter('price(gbp)', null, 'users.id = 1 AND users.price = {price(gbp)}'))
        );
    }
}
