<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportControllerTest extends LaravelTestCase
{

    #[Test]
    public function can_view_report_index(): void
    {
        factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Test Report',
                    ]
                ],
            ]);
    }

    #[Test]
    public function can_view_all_reports(): void
    {
        factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.all'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Test Report',
                    ]
                ],
            ]);
    }

    #[Test]
    public function can_show_report(): void
    {
        $report = factory(Report::class)->create(['name' => 'Test Report']);

        $response = $this->getJson(route('report.show', ['report' => $report->getKey()]));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Test Report',
                ],
            ]);
    }

    #[Test]
    public function can_render_a_report(): void
    {
        $report = factory(Report::class)->create();

        $response = $this->postJson(route('report.render', ['report' => $report->getKey()]));

        $response->assertStatus(200);
    }

    #[Test]
    public function can_generate_report_export_uri(): void
    {
        $report = factory(Report::class)->create();

        $response = $this->actingAs(new User)->postJson(route('report.export.generate', ['report' => $report->getKey()]));

        $response->assertStatus(200);
    }

    #[Test]
    public function can_export_report(): void
    {
        $report = factory(Report::class)->create();

        $request = new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]);

        $response = $this->actingAs(new User)->get($report->getExportLink($request));

        $response->assertStatus(200);
    }

    #[Test]
    public function can_export_report_authorises_use(): void
    {
        Auth::login(new User);

        $report = factory(Report::class)->create();

        $request = new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]);

        $response = $this->get($report->getExportLink($request));

        $response->assertStatus(200);
    }

    #[Test]
    public function export_report_with_invalid_or_modified_signature_returns_unauthorized(): void
    {
        $report = factory(Report::class)->create();

        $request = new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class)
        ]);

        $response = $this->actingAs(new User)->get($report->getExportLink($request) . '&uuid=1');

        $response->assertStatus(401);
    }

    #[Test]
    public function can_view_report_preview(): void
    {
        Auth::login(new User());

        $response = $this->postJson(route('report.preview', [
            'report' => factory(Report::class)->create()
        ]), []);

        $response->assertStatus(200);
    }

    #[Test]
    public function returns_sql_for_admins(): void
    {
        $adminUser = new User();
        $adminUser->setIsAdmin(true);

        Auth::login($adminUser);

        $response = $this->postJson(route('report.preview', [
            'report' => factory(Report::class)->create()
        ]), []);

        $response->assertStatus(200);
        $this->assertArrayHasKey('raw', $response->json());

        $nonAdminUser = new User();
        $nonAdminUser->setIsAdmin(false);

        Auth::login($nonAdminUser);

        $response = $this->postJson(route('report.preview', [
            'report' => factory(Report::class)->create()
        ]), []);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('raw', $response->json());
    }

    #[Test]
    public function render_does_not_run_injected_sql(): void
    {
        $this->createFakeUser(2);

        $report = factory(Report::class)->create(['where' => "users.name = '{name}'"]);

        factory(ReportField::class)->create([
            'report_id' => $report->id,
            'type' => 'text',
            'alias' => 'name',
        ]);

        $response = $this->postJson(route('report.render', ['report' => $report->getKey()]), [
            'name' => "x' OR '1'='1",
        ]);

        $response->assertStatus(200);
        $this->assertSame(0, $response->json('data.total'));
    }

    #[Test]
    public function export_link_uses_the_logged_in_user_id(): void
    {
        [$user, $other] = $this->createFakeUser(2)->all();

        $report = factory(Report::class)->create();
        $otherReport = factory(Report::class)->create();

        $this->actingAs($user);

        $link = $report->getExportLink(new Request([
            'uid' => $other->getKey(),
            'report' => $otherReport->getKey(),
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class),
        ]));

        parse_str(parse_url($link, PHP_URL_QUERY), $query);

        $this->assertEquals($user->getKey(), $query['uid']);
        $this->assertStringContainsString('/report/' . $report->getKey() . '/export', $link);
    }

    #[Test]
    public function invalid_signature_does_not_log_the_guest_in(): void
    {
        config(['auth.providers.users.model' => User::class]);

        $user = $this->createFakeUser();

        $report = factory(Report::class)->create();

        $this->get(route('report.export', [
            'report' => $report->getKey(),
            'uid' => $user->getKey(),
            'signature' => 'not-valid',
        ]))->assertStatus(401);

        $this->assertGuest();
    }

    #[Test]
    public function numeric_request_keys_cannot_add_raw_text_to_the_export_link(): void
    {
        config(['auth.providers.users.model' => User::class]);

        [$user, $other] = $this->createFakeUser(2)->all();

        $report = factory(Report::class)->create();

        $this->actingAs($user);

        $link = $report->getExportLink(new Request([
            0 => 'uid=' . $other->getKey() . '&expires=0',
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class),
        ]));

        $this->assertSame(1, substr_count($link, 'uid='));
        $this->assertStringNotContainsString('expires=0', $link);

        // A guest following the link is logged in as the link owner only
        $this->app['auth']->forgetGuards();

        $this->get($link)->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

}
