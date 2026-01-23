<?php

namespace App\Traits\Enum;

trait HasLookup
{
    public static function options(): array
    {
        return [];
    }

    public static function lookup(): array
    {
        $options = self::options();

        return array_map(fn ($case) => [
            'key' => $case->value,
            'value' => $options[$case->value] ?? $case->value,
        ], self::cases());
    }
}
