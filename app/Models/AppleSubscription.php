<?php

namespace App\Models;

use App\Enums\AppleSubSubscriptionStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $transaction_id
 * @property string $original_transaction_id
 * @property string $product_id
 * @property string $environment
 * @property AppleSubSubscriptionStatusEnum $status
 * @property string|null $purchase_date
 * @property string|null $expires_date
 * @property string|null $price_currency
 * @property string|null $price_amount
 * @property array|null $raw_transaction decoded signedTransactionInfo
 * @property string|null $raw_renewal decoded signedRenewalInfo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder<static>|AppleSubscription newModelQuery()
 * @method static Builder<static>|AppleSubscription newQuery()
 * @method static Builder<static>|AppleSubscription query()
 * @method static Builder<static>|AppleSubscription whereCreatedAt($value)
 * @method static Builder<static>|AppleSubscription whereEnvironment($value)
 * @method static Builder<static>|AppleSubscription whereExpiresDate($value)
 * @method static Builder<static>|AppleSubscription whereId($value)
 * @method static Builder<static>|AppleSubscription whereOriginalTransactionId($value)
 * @method static Builder<static>|AppleSubscription wherePriceAmount($value)
 * @method static Builder<static>|AppleSubscription wherePriceCurrency($value)
 * @method static Builder<static>|AppleSubscription whereProductId($value)
 * @method static Builder<static>|AppleSubscription wherePurchaseDate($value)
 * @method static Builder<static>|AppleSubscription whereRawRenewal($value)
 * @method static Builder<static>|AppleSubscription whereRawTransaction($value)
 * @method static Builder<static>|AppleSubscription whereStatus($value)
 * @method static Builder<static>|AppleSubscription whereTransactionId($value)
 * @method static Builder<static>|AppleSubscription whereUpdatedAt($value)
 * @method static Builder<static>|AppleSubscription whereUserId($value)
 *
 * @property AppleSubSubscriptionStatusEnum $apple_subscription_status
 * @property-read \App\Models\AppUser|null $user
 *
 * @mixin \Eloquent
 */
class AppleSubscription extends BaseModel
{
    protected $table = 'apple_subscriptions';

    protected $casts = [
        'apple_subscription_status' => AppleSubSubscriptionStatusEnum::class,
        'raw_transaction' => 'array',
    ];

    /**
     * @return BelongsTo<AppUser,$this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'id');
    }
}
