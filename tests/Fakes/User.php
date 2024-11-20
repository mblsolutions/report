<?php

namespace MBLSolutions\Report\Tests\Fakes;

use Illuminate\Foundation\Auth\User as AuthUser;
use MBLSolutions\Report\Interfaces\IsAuthenticatableAnAdmin;

class User extends AuthUser implements IsAuthenticatableAnAdmin
{
    private $isAdmin = false;

    protected $guarded = false;

    public function setIsAdmin(bool $isAdmin = true): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function isAuthenticatableAnAdmin(): bool
    {
        return $this->isAdmin;
    }
}