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
    /** @var Authenticated $middleware */
    protected $middleware;

    /** {@inheritdoc} **/
    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new Authenticated;
    }
    
    /** @test **/
    public function can_handle_middleware(): void
    {
        Auth::login(new User);

        $builder = DB::table('users');

        $this->assertEquals($builder, $this->middleware->handle($builder));
    }

    /** @test **/
    public function if_user_not_authenticated_exception_thrown(): void
    {
        $this->expectException(UnauthorizedException::class);

        $builder = DB::table('users');

        $this->middleware->handle($builder);
    }

    /** @test **/
    public function can_check_if_field_should_be_shown(): void
    {
        $this->assertTrue($this->middleware->field(new ReportField()));
    }

}