<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Enums\ApiCallEnvironmentEnum;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\AppUserResource;
use App\Models\AppUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends BaseAPIController
{
    public function update(Request $request): JsonResponse
    {
        $name = $request->input('name');

        /**
         * @var AppUser $user
         */
        $user = auth()->user();

        if (! empty($name)) {
            $user->name = $name;
        }

        $user->save();

        return $this->responseJsonSuccess(['user' => AppUserResource::make($user)], 'user updated successfully');
    }

    public function lookups(): JsonResponse
    {
        $data = [
            'api_call_environments' => ApiCallEnvironmentEnum::lookup(),
        ];

        return $this->responseJsonSuccess($data, 'user lookups retrieved successfully');

    }

    public function userDetails(): JsonResponse
    {
        $user = auth()->user();
        $token = $user->createToken('user_app_token')->accessToken;

        $user->access_token = $token;

        return $this->responseJsonSuccess(AppUserResource::make($user), 'User Details retrieved successfully');
    }
}
