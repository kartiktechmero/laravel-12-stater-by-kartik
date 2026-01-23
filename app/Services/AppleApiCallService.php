<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApiCallEnvironmentEnum;
use App\Http\Integrations\Apple\ApplePaymentConnector;
use App\Http\Integrations\Apple\Requests\AppleSubscriptionRequest;
use App\Managers\AppleSubscriptionManager;
use App\Models\AppUser;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppleApiCallService
{
    public static function getSubscriptionBaseUrlFromEnvironment(?ApiCallEnvironmentEnum $app_env): string
    {
        return match ($app_env) {
            ApiCallEnvironmentEnum::LIVE => 'https://api.storekit.itunes.apple.com',
            default => 'https://api.storekit-sandbox.itunes.apple.com/'
        };

    }

    public static function addNewSubscription(string $transaction_id, AppUser $user): array
    {
        $res = ['status' => false];

        Log::info('Add subscription request received', [
            'transaction_id' => $transaction_id,
            'user_id' => $user->id,
        ]);

        $exists = AppleSubscriptionManager::existTransection($transaction_id);

        if ($exists) {
            $res['message'] = 'Transaction ID already exists';

            Log::warning('Duplicate transaction detected', [
                'transaction_id' => $transaction_id,
                'user_id' => $user->id,
            ]);

            return $res;
        }

        $subscription = self::getSubscription($transaction_id, $user);

        if (! ($subscription['status'] ?? false)) {
            Log::error('Failed to get subscription', [
                'transaction_id' => $transaction_id,
                'user_id' => $user->id,
                'message' => $subscription['message'] ?? 'Unknown failure',
            ]);

            $res['message'] = $subscription['message'] ?? 'Something went wrong';

            return $res;
        }

        try {
            DB::transaction(function () use ($user, $subscription, $transaction_id) {
                $apple_sub = AppleSubscriptionManager::createFromAppleDecoded($subscription['decode_data'], $user);

                $user->apple_subscription_status = $apple_sub->status;
                $user->save();

                Log::info('Subscription saved successfully', [
                    'transaction_id' => $transaction_id,
                    'user_id' => $user->id,
                    'subscription_status' => $apple_sub->status,
                ]);
            });

            $res['status'] = true;
            $res['message'] = 'Subscription stored successfully';

        } catch (\Throwable $e) {
            Log::error('Error saving subscription', [
                'transaction_id' => $transaction_id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $res['message'] = 'Error saving subscription';
        }

        return $res;
    }

    public static function getSubscription(string $transaction_id, AppUser $user): array
    {
        $res = ['status' => false];

        Log::info('Fetching Apple subscription', [
            'transaction_id' => $transaction_id,
            'user_id' => $user->id,
        ]);

        try {
            $connector = new ApplePaymentConnector($user);
            $request = new AppleSubscriptionRequest($transaction_id);

            $response = $connector->send($request);

            if (! $response->successful()) {
                $error = $response->json();
                $error_message = $error['errorMessage'] ?? 'Failed to fetch subscription';

                Log::error('Apple API returned error', [
                    'transaction_id' => $transaction_id,
                    'user_id' => $user->id,
                    'error' => $error_message,
                    'raw_response' => $error,
                ]);

                $res['message'] = $error_message;

                return $res;
            }

            $data = $response->json('data');

            if (empty($data[0]['lastTransactions'][0])) {
                Log::warning('Apple API returned empty lastTransactions', [
                    'transaction_id' => $transaction_id,
                    'user_id' => $user->id,
                ]);

                $res['message'] = 'Failed to get subscription (empty transaction)';

                return $res;
            }

            $lastTransaction = $data[0]['lastTransactions'][0];
            $signedTransaction = $lastTransaction['signedTransactionInfo'];

            $decodedPayload = self::decodeSignedTransaction($signedTransaction);

            Log::info('Subscription decoded successfully', [
                'transaction_id' => $transaction_id,
                'user_id' => $user->id,
                'expires_date' => $decodedPayload['expiresDate'] ?? null,
            ]);

            $res['status'] = true;
            $res['decode_data'] = $decodedPayload;
            $res['message'] = 'Subscription retrieved';

        } catch (Exception $e) {
            Log::error('Exception while fetching Apple subscription', [
                'transaction_id' => $transaction_id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $res['message'] = $e->getMessage();
        }

        return $res;
    }

    public static function decodeSignedTransaction(string $signedTransaction): array
    {
        try {
            // JWTs have three segments: header, payload, signature
            $segments = explode('.', $signedTransaction);
            if (count($segments) !== 3) {
                throw new Exception('Invalid JWT format');
            }

            // Decode the payload (2nd segment of JWT)
            $payload = json_decode(base64_decode(strtr($segments[1], '-_', '+/')), true);

            // Convert timestamps to readable format
            if (isset($payload['purchaseDate'])) {
                $payload['purchaseDate'] = date('Y-m-d H:i:s', $payload['purchaseDate'] / 1000);
            }

            if (isset($payload['expiresDate'])) {
                $payload['expiresDate'] = date('Y-m-d H:i:s', $payload['expiresDate'] / 1000);
            }

            return $payload;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function generateJWT(): string
    {

        $appleConfig = [
            'privateKey' => str_replace('\\n', "\n", config('services.apple.private_key')),
            'keyId' => config('services.apple.key_id'),
            'issuerId' => config('services.apple.issuer_id'),
            'bundleId' => config('services.apple.bundle_id'),
        ];

        $privateKey = $appleConfig['privateKey'];
        $keyId = $appleConfig['keyId'];
        $issuerId = $appleConfig['issuerId'];

        $time = time();

        $payload = [
            'iss' => $issuerId,
            'iat' => $time,
            'exp' => $time + (20 * 60), // valid for 20 mins max
            'aud' => 'appstoreconnect-v1',
            // optional but sometimes useful for in-app purchases:
            'bid' => $appleConfig['bundleId'],
        ];

        return JWT::encode(
            $payload,
            $privateKey,
            'ES256',
            $keyId
        );
    }
}
