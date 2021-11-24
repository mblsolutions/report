<?php

namespace MBLSolutions\Report\Tests\Unit\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MBLSolutions\Report\Exceptions\UnauthorizedException;
use MBLSolutions\Report\Middleware\Authenticated;
use MBLSolutions\Report\Models\ReportField;
use MBLSolutions\Report\Tests\Fakes\User;
use MBLSolutions\Report\Tests\LaravelTestCase;

class AuthenticatedTest extends LaravelTestCase
{
    protected User $user;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User;
    }

    /** @test **/
    public function can_handle_middleware(): void
    {
        $middleware = new Authenticated($this->user);

        Auth::login($this->user);

        $builder = DB::table('users');

        $this->assertEquals($builder, $middleware->handle($builder));
    }

    /** @test **/
    public function if_user_not_authenticated_exception_thrown(): void
    {
        $this->expectException(UnauthorizedException::class);

        $middleware = new Authenticated();

        $builder = DB::table('users');

       $middleware->handle($builder);
    }

    /** @test **/
    public function can_check_if_field_should_be_shown(): void
    {
        $middleware = new Authenticated();

        $this->assertTrue($middleware->field(new ReportField()));
    }

}