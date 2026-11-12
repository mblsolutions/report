<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use MBLSolutions\Report\Http\Resources\ReportFieldResource;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportFieldType;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Repositories\ManageReportRepository;
use MBLSolutions\Report\Services\BuildReportService;
use MBLSolutions\Report\Tests\Fakes\Middleware\HidesFieldsMiddleware;
use MBLSolutions\Report\Tests\Fakes\OptionUser;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class SelectColumnsTest extends LaravelTestCase
{

    #[Test]
    public function saving_refuses_a_select_column_that_is_not_allowed(): void
    {
        $this->assertTrue($this->validate('password', 'name')->errors()->has('fields.0.model_select_value'));
        $this->assertTrue($this->validate('id', 'password')->errors()->has('fields.0.model_select_name'));
        $this->assertFalse($this->validate('id', 'name')->fails());

        config(['report.select_columns' => ['id', 'email']]);

        $this->assertFalse($this->validate('id', 'email')->fails());
        $this->assertTrue($this->validate('id', 'name')->fails());
    }

    #[Test]
    public function a_field_with_a_column_that_is_not_allowed_gets_no_options(): void
    {
        $this->createFakeUser(2);

        $allowed = (new ReportFieldResource($this->selectField('id', 'name')))->resolve();
        $refused = (new ReportFieldResource($this->selectField('id', 'password')))->resolve();

        $this->assertCount(2, $allowed['options']);
        $this->assertArrayNotHasKey('options', $refused);
    }

    #[Test]
    public function the_name_lookup_is_skipped_for_a_field_a_report_middleware_hides(): void
    {
        $this->actingAsAdmin();

        $user = $this->createFakeUser();
        $field = $this->selectField('id', 'name');

        $shown = (new BuildReportService($field->report, []))->getFormattedParameters(['user' => $user->getKey()]);
        $this->assertSame($user->name, $shown['user']['value']);

        config(['report.middleware' => [HidesFieldsMiddleware::class], 'report.tenant_middleware' => [HidesFieldsMiddleware::class]]);
        factory(ReportMiddleware::class)->create(['report_id' => $field->report_id, 'middleware' => HidesFieldsMiddleware::class]);

        $hidden = (new BuildReportService(Report::query()->find($field->report_id), []))->getFormattedParameters(['user' => $user->getKey()]);
        $this->assertSame($user->getKey(), $hidden['user']['value']);
    }

    private function validate(string $value, string $name)
    {
        return (new ManageReportRepository())->validateReportRequest(new Request([
            'name' => 'Test Report',
            'description' => 'A test report.',
            'connection' => 'sqlite',
            'display_limit' => 25,
            'show_data' => true,
            'show_totals' => false,
            'active' => true,
            'table' => 'users',
            'fields' => [[
                'label' => 'User',
                'alias' => 'user',
                'type' => ReportFieldType::SELECT,
                'model' => config('report.models')[0],
                'model_select_value' => $value,
                'model_select_name' => $name,
            ]],
        ]));
    }

    private function selectField(string $value, string $name): ReportField
    {
        return factory(ReportField::class)->create([
            'label' => 'User',
            'alias' => 'user',
            'type' => ReportFieldType::SELECT,
            'model' => OptionUser::class,
            'model_select_value' => $value,
            'model_select_name' => $name,
        ]);
    }
}
