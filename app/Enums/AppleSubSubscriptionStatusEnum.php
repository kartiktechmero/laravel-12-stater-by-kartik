<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum\HasLookup;

enum AppleSubSubscriptionStatusEnum: string
{
    use HasLookup;

    case NONE = 'none';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public static function options(): array
    {
        return [
            self::NONE->value => 'None',
            self::ACTIVE->value => 'Active',
            self::EXPIRED->value => 'Expired',
            self::CANCELLED->value => 'Cancelled',
        ];
    }
}
