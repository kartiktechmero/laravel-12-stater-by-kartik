<?php

namespace App\Managers;

use App\Enums\AppleSubSubscriptionStatusEnum;
use App\Models\AppleSubscription;
use App\Models\AppUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @implements BaseManager<AppleSubscription>
 */
class AppleSubscriptionManager implements BaseManager
{
    /**
     * @return Builder<AppleSubscription>
     */
    public static function baseQuery(): Builder
    {
        return AppleSubscription::query();
    }

    public static function createFromAppleDecoded(array $decodedPayload, AppUser $appUser): AppleSubscription
    {

        $appleSubscription = new AppleSubscription;
        $appleSubscription->user_id = $appUser->id;
        $appleSubscription->transaction_id = $decodedPayload['transactionId'];
        $appleSubscription->original_transaction_id = $decodedPayload['originalTransactionId'];
        $appleSubscription->product_id = $decodedPayload['productId'];
        $appleSubscription->environment = $decodedPayload['environment'];
        $appleSubscription->status = self::getStatusFromExpiryDate($decodedPayload['expiresDate']);
        $appleSubscription->purchase_date = $decodedPayload['purchaseDate'];
        $appleSubscription->expires_date = $decodedPayload['expiresDate'];
        $appleSubscription->price_currency = $decodedPayload['currency'];
        $appleSubscription->price_amount = $decodedPayload['price'];
        $appleSubscription->raw_transaction = $decodedPayload;
        $appleSubscription->save();

        return $appleSubscription;
    }

    public static function getStatusFromExpiryDate(string $expiryDate): AppleSubSubscriptionStatusEnum
    {
        $expiresAt = Carbon::parse($expiryDate);

        return $expiresAt->greaterThan(now()) ? AppleSubSubscriptionStatusEnum::ACTIVE : AppleSubSubscriptionStatusEnum::EXPIRED;
    }

    public static function updateStatusFromAppleDecoded(string $expiryDate, AppleSubscription $appleSubscription, AppUser $user): bool
    {
        try {
            DB::transaction(function () use ($expiryDate, $user, $appleSubscription) {
                $appleSubscription->status = self::getStatusFromExpiryDate($expiryDate);
                $appleSubscription->save();
                Log::info('Subscription updated to status: '.$appleSubscription->status->value.' for user :'.$appleSubscription->user_id);

                $user->apple_subscription_status = $appleSubscription->status;
                $user->save();
            });

            return true;
        } catch (\Exception $exception) {
            Log::error('updateStatusFromAppleDecoded :'.$exception->getMessage(), $exception->getTrace());
        }

        return false;
    }

    public static function existTransection(string $subscriptionId): bool
    {
        return self::baseQuery()->where('original_transaction_id', $subscriptionId)->exists();
    }
}
