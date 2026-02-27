<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ConnectionControllerTest extends LaravelTestCase
{

    #[Test]
    public function can_get_available_connections(): void
    {
        $response = $this->getJson(route('report.connection.list'));

        $response->assertStatus(200);
    }

}