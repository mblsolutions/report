<?php

namespace MBLSolutions\Report\Tests\Feature;

use MBLSolutions\Report\Tests\LaravelTestCase;

class ManageSettingsControllerTest extends LaravelTestCase
{

    /** @test **/
    public function can_get_manage_report_settings(): void
    {
        $response = $this->getJson('/report/manage/settings');

        $response->assertStatus(200);
    }

}