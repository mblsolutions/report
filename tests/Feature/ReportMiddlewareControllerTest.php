<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;

class ReportMiddlewareControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_get_available_middleware(): void
    {
        $response = $this->getJson(route('report.middleware.list'));

        $response->assertStatus(200);
    }

}