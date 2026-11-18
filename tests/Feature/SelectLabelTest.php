<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MBLSolutions\Report\Jobs\RenderReport;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportFieldType;
use MBLSolutions\Report\Models\ReportJob;
use MBLSolutions\Report\Models\ReportMiddleware;
use MBLSolutions\Report\Tests\Fakes\TenantOptionUser;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class SelectLabelTest extends LaravelTestCase
{
    private const OTHER_TENANT = 150;

    protected Report $report;

    protected User $member;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        config(['report.authenticatable_model' => User::class]);

        $this->member = $this->createFakeUser();
        DB::table('users')->insert(['id' => self::OTHER_TENANT, 'name' => 'other tenant']);

        $this->report = factory(Report::class)->create();
        factory(ReportMiddleware::class)->create(['report_id' => $this->report->getKey()]);
        factory(ReportField::class)->create([
            'report_id' => $this->report->getKey(),
            'label' => 'User',
            'alias' => 'user',
            'type' => ReportFieldType::SELECT,
            'model' => TenantOptionUser::class,
            'model_select_value' => 'id',
            'model_select_name' => 'name',
        ]);
    }

    #[Test]
    public function preview_shows_no_label_for_another_tenants_value(): void
    {
        $this->actingAs($this->member)
            ->postJson(route('report.preview', ['report' => $this->report->getKey()]), ['user' => self::OTHER_TENANT])
            ->assertStatus(200)
            ->assertJsonPath('parameters.user.value', self::OTHER_TENANT);
    }

    #[Test]
    public function preview_shows_the_label_for_the_users_own_value(): void
    {
        $this->actingAs($this->member)
            ->postJson(route('report.preview', ['report' => $this->report->getKey()]), ['user' => $this->member->getKey()])
            ->assertStatus(200)
            ->assertJsonPath('parameters.user.value', $this->member->name);
    }

    #[Test]
    public function admins_get_the_label_for_any_value(): void
    {
        $admin = $this->createFakeUser();
        $admin->setIsAdmin();

        $this->actingAs($admin)
            ->postJson(route('report.preview', ['report' => $this->report->getKey()]), ['user' => self::OTHER_TENANT])
            ->assertStatus(200)
            ->assertJsonPath('parameters.user.value', 'other tenant');
    }

    #[Test]
    public function a_queued_job_labels_only_the_owners_own_values(): void
    {
        $this->app['auth']->forgetGuards();

        dispatch(new RenderReport('aa5615b7-8489-4831-ad00-f50ae770b619', $this->report, ['user' => self::OTHER_TENANT], $this->member->getKey()));
        dispatch(new RenderReport('bb5615b7-8489-4831-ad00-f50ae770b619', $this->report, ['user' => $this->member->getKey()], $this->member->getKey()));

        $foreign = ReportJob::query()->findOrFail('aa5615b7-8489-4831-ad00-f50ae770b619');
        $own = ReportJob::query()->findOrFail('bb5615b7-8489-4831-ad00-f50ae770b619');

        $this->assertSame(self::OTHER_TENANT, $foreign->formatted_parameters['user']['value']);
        $this->assertSame($this->member->name, $own->formatted_parameters['user']['value']);
        $this->assertNull(Auth::user());
    }
}
