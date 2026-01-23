<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApiCallEnvironmentEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class AppUser extends Authenticatable
{
    use HasApiTokens;
    use SoftDeletes;

    protected $table = 'app_users';

    protected $guarded = ['id'];

    protected $casts = [
        'environment' => ApiCallEnvironmentEnum::class,
        'bundle_id' => 'string',
    ];

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * @return BelongsTo<AppleSubscription,$this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(AppleSubscription::class, 'id', 'user_id');
    }
}
