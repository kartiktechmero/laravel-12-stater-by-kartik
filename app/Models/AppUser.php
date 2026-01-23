<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApiCallEnvironmentEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * @property int $id
 * @property string $ud_id From apple take transection it which is uniq one
 * @property string|null $token_id
 * @property string $apple_subscription_status take from AppleSubSubscriptionStatusEnum
 * @property string|null $social_id
 * @property string|null $ip
 * @property string|null $current_app_version
 * @property string|null $email
 * @property string|null $name
 * @property ApiCallEnvironmentEnum|null $environment
 * @property string|null $bundle_id
 * @property string|null $access_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $oauthApps
 * @property-read int|null $oauth_apps_count
 * @property-read \App\Models\AppleSubscription|null $subscription
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereAppleSubscriptionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereCurrentAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereSocialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereUdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AppUser withoutTrashed()
 *
 * @mixin \Eloquent
 */
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
