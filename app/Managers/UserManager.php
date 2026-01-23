<?php

namespace App\Managers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @implements BaseManager<User>
 */
class UserManager implements BaseManager
{
    /**
     * @return Builder<User>
     */
    public static function baseQuery(): Builder
    {
        return User::query();
    }
}
