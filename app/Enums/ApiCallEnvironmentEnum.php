<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum\HasLookup;

enum ApiCallEnvironmentEnum: string
{
    use HasLookup;

    case SANDBOX = 'sandbox';
    case TEST_FLIGHT = 'test_flight';
    case LIVE = 'live';

    public static function options(): array
    {
        return [
            self::SANDBOX->value => 'Sandbox',
            self::TEST_FLIGHT->value => 'Test Flight',
            self::LIVE->value => 'Live',
        ];
    }
}
