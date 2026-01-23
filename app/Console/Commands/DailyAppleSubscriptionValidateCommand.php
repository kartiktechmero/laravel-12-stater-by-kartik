<?php

namespace App\Console\Commands;

use App\Managers\AppleSubscriptionManager;
use App\Services\AppleApiCallService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class DailyAppleSubscriptionValidateCommand extends Command
{
    protected $signature = 'app:daily-apple-subscription-validate';

    protected $description = 'Daily validation of all Apple subscriptions based on their original transaction IDs';

    public function handle(): int
    {
        Log::info('[DailyAppleSubscriptionValidate] Started subscription check');

        try {
            $subscriptions = AppleSubscriptionManager::baseQuery()
                ->with(['user'])
                ->get();

            $total = $subscriptions->count();
            Log::info('[DailyAppleSubscriptionValidate] Total subscriptions found', [
                'count' => $total,
            ]);

            $progressBar = $this->output->createProgressBar($total);

            foreach ($subscriptions as $subscription) {
                $progressBar->advance();

                $originalId = $subscription->original_transaction_id;
                $user = $subscription->user;

                Log::info('[DailyAppleSubscriptionValidate] Checking subscription', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'original_transaction_id' => $originalId,
                ]);

                // Call Apple API
                $apiRes = AppleApiCallService::getSubscription($originalId, $user);

                if (isset($apiRes['status']) && $apiRes['status'] === true && isset($apiRes['decode_data'])) {
                    Log::info('[DailyAppleSubscriptionValidate] Apple response received', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $user->id,
                        'expiresDate' => $apiRes['decode_data']['expiresDate'] ?? null,
                    ]);

                    AppleSubscriptionManager::updateStatusFromAppleDecoded(
                        $apiRes['decode_data']['expiresDate'],
                        $subscription,
                        $user
                    );

                } else {
                    Log::error('[DailyAppleSubscriptionValidate] Failed to update subscription', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $user->id,
                        'original_transaction_id' => $originalId,
                        'error_message' => $apiRes['message'] ?? 'Unknown error',
                        'response' => $apiRes,
                    ]);
                }
            }

            $progressBar->finish();
            Log::info('[DailyAppleSubscriptionValidate] Completed subscription check');

        } catch (\Throwable $e) {
            Log::critical('[DailyAppleSubscriptionValidate] Unhandled exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return CommandAlias::SUCCESS;
    }
}
