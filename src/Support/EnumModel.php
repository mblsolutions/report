<?php

namespace MBLSolutions\Report\Support;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;

abstract class EnumModel
{

    /**
     * Get All Enumerators
     *
     * @return Collection
     * @throws ReflectionException
     */
    public static function all(): Collection
    {
        $reflectionClass = new ReflectionClass(static::class);

        return collect($reflectionClass->getConstants());
    }

}