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
     */
    public static function all(): Collection
    {
        $reflectionClass = new ReflectionClass(static::class);

        return collect($reflectionClass->getConstants());
    }

    /**
     * Get a Readable name for the ENUM
     *
     * @param string $enum
     * @return string
     */
    public static function name(string $enum): string
    {
        $parts = array_map('ucfirst', explode('_', $enum));

        return implode('', $parts);
    }

    /**
     * Collection of Options
     *
     * @param string $value
     * @param string $name
     * @return Collection
     */
    public static function options(string $value = 'id', string $name = 'name'): Collection
    {
        $options = collect();

        self::all()->each(static function ($enum) use ($options) {
            $options->add(['id' => $enum, 'name' => self::name($enum)]);
        });

        return $options;
    }

}