<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class DataTypeControllerTest extends LaravelTestCase
{

    #[Test]
    public function can_get_available_data_types(): void
    {
        $response = $this->getJson(route('report.data.type.list'));

        $response->assertStatus(200);
    }

}