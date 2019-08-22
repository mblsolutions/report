<?php

namespace MBLSolutions\Report\Tests\Unit;

use MBLSolutions\Report\Driver\Export\PrintExport;
use MBLSolutions\Report\Models\ReportSelectField;
use MBLSolutions\Report\Support\ConfigModel;
use MBLSolutions\Report\Tests\LaravelTestCase;

class ConfigModelTest extends LaravelTestCase
{

    /** @test **/
    public function can_create_a_report_select_field_model(): void
    {
        $configModel = new ReportSelectField;

        $this->assertInstanceOf(ConfigModel::class, $configModel);
    }

    /** @test **/
    public function can_get_all_config_models(): void
    {
        $configModel = new ReportSelectField();

        $this->assertEquals([
            ['value' => 'App\User', 'name' => 'User']
        ], $configModel->all()->toArray());
    }

    /** @test **/
    public function can_format_namespace_name(): void
    {
        $configModel = new ReportSelectField();

        $this->assertEquals('User', $configModel->formatName('\App\User'));
    }

    /** @test **/
    public function can_format_class_name(): void
    {
        $configModel = new ReportSelectField();

        $this->assertEquals('Auth User', $configModel->formatClassName('\App\AuthUser'));
    }

    /** @test **/
    public function if_class_has_name_property_use_that_when_formatting_class_name(): void
    {
        $configModel = new ReportSelectField();

        $this->assertEquals('Print Report', $configModel->formatClassName(PrintExport::class));
    }

}