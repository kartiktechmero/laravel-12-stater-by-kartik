<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel query()
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    /**
     * By default, allow mass assignment for all fields.
     * You can override in child models if needed.
     */
    protected $guarded = [];

    /**
     * Common traits / methods for all models can also go here.
     */
}
