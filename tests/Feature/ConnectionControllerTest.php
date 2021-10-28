<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;

class ConnectionControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_get_available_connections(): void
    {
        $response = $this->getJson(route('report.connection.list'));

        $response->assertStatus(200);
    }

}