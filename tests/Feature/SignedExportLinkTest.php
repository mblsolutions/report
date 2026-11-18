<?php

namespace MBLSolutions\Report\Tests\Feature;

use Illuminate\Http\Request;
use MBLSolutions\Report\Models\Report;
use MBLSolutions\Report\Models\ReportExportDrivers;
use MBLSolutions\Report\Tests\Fakes\ExportDriver\FakeExportDriver;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;
use PHPUnit\Framework\Attributes\Test;

class SignedExportLinkTest extends LaravelTestCase
{
    protected User $owner;

    protected User $other;

    protected string $link;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.providers.users.model' => User::class]);

        [$this->owner, $this->other] = $this->createFakeUser(2)->all();
        $this->owner->setIsAdmin();
        $this->other->setIsAdmin();

        $this->actingAs($this->owner);

        $this->link = factory(Report::class)->create()->getExportLink(new Request([
            'driver' => ReportExportDrivers::hashDriverValue(FakeExportDriver::class),
        ]));

        $this->app['auth']->forgetGuards();
    }

    #[Test]
    public function a_guest_gets_401_and_stays_a_guest(): void
    {
        $this->get($this->link)->assertStatus(401);

        $this->assertGuest();
    }

    #[Test]
    public function another_user_gets_403(): void
    {
        $this->actingAs($this->other)->get($this->link)->assertStatus(403);
    }

    #[Test]
    public function the_link_owner_gets_the_file(): void
    {
        $this->actingAs($this->owner)->get($this->link)->assertStatus(200);
    }
}
