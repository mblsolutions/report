<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;

class ModelControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_get_available_models(): void
    {
        $response = $this->getJson(route('report.model.list'));

        $response->assertStatus(200);
    }

}