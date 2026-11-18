<?php

namespace MBLSolutions\Report\Tests\Fakes;

use Illuminate\Foundation\Auth\User as AuthUser;
use MBLSolutions\Report\Interfaces\IsAuthenticatableAnAdmin;

class User extends AuthUser implements IsAuthenticatableAnAdmin
{
    private $isAdmin = false;

    /** Saved users marked as admin, so a copy loaded from the DB is an admin too */
    public static array $adminIds = [];

    protected $guarded = false;

    public function setIsAdmin(bool $isAdmin = true): void
    {
        $this->isAdmin = $isAdmin;

        if ($this->getKey() !== null) {
            static::$adminIds = array_diff(static::$adminIds, [$this->getKey()]);

            if ($isAdmin) {
                static::$adminIds[] = $this->getKey();
            }
        }
    }

    public function isAuthenticatableAnAdmin(): bool
    {
        return $this->isAdmin || in_array($this->getKey(), static::$adminIds, true);
    }
}