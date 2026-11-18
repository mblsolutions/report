<?php

namespace MBLSolutions\Report\Tests\Fakes;

use Illuminate\Foundation\Auth\User as AuthUser;

/** A user model without IsAuthenticatableAnAdmin */
class PlainUser extends AuthUser
{
    protected $table = 'users';

    protected $guarded = false;
}
