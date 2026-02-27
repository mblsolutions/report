<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportMiddlewareControllerTest extends LaravelTestCase
{

    #[Test]
    public function can_get_available_middleware(): void
    {
        $response = $this->getJson(route('report.middleware.list'));

        $response->assertStatus(200);
    }

}