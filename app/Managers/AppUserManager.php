<?php

namespace App\Managers;

use App\Models\AppUser;
use Illuminate\Database\Eloquent\Builder;

/**
 * @implements BaseManager<AppUser>
 */
class AppUserManager implements BaseManager
{
    /**
     * @return Builder<AppUser>
     */
    public static function baseQuery(): Builder
    {
        return AppUser::query();
    }

    public static function getByUdId(string|int $ud_id): ?AppUser
    {
        return self::baseQuery()->where('ud_id', $ud_id)->first();
    }

    public static function getBySocialId(string|int $social_id): ?AppUser
    {
        return self::baseQuery()->where('social_id', $social_id)->first();
    }

}
