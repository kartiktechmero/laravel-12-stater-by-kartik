<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Subscription;

use App\Http\Controllers\BaseAPIController;
use App\Http\Requests\AddSubscriptionRequest;
use App\Services\AppleApiCallService;
use Illuminate\Http\JsonResponse;

class AppleSubscriptionController extends BaseAPIController
{
    public function __construct() {}

    public function addSubscription(AddSubscriptionRequest $request): JsonResponse
    {
        $transection_id = $request->input('transection_id');

        $transactionDetails = AppleApiCallService::addNewSubscription($transection_id, auth()->user());
        if (isset($transactionDetails['status']) && $transactionDetails['status']) {
            return $this->responseJsonSuccess([], $transactionDetails['message']);
        }

        return $this->responseJsonError([], (string) $transactionDetails['message']);
    }
}
