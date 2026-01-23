<?php

namespace App\Managers;

use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
interface BaseManager
{
    /**
     * @return Builder<TModel>
     */
    public static function baseQuery(): Builder;
}
