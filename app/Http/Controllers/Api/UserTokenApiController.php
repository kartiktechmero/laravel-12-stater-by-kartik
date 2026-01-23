<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ApiCallEnvironmentEnum;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\AppUserResource;
use App\Managers\AppUserManager;
use App\Models\AppUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class UserTokenApiController extends BaseAPIController
{
    public function store(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'ud_id' => 'required',
                'token_id' => ['nullable', 'string'],
                'current_app_version' => 'required',
                'environment' => ['required', new Enum(ApiCallEnvironmentEnum::class)],
                'bundle_id' => ['required'],
            ]);
            if ($validator->fails()) {
                $errorsArray = $this->validationResponseError($validator->errors()->toArray());

                return $this->responseJsonError($errorsArray);
            }

            $udId = $request->ud_id;
            $ipAddress = $request->header('cf-connecting-ip') ?? $request->header('client-ip') ?? $request->ip();

            $data = $request->only('ud_id', 'token_id', 'current_app_version', 'environment', 'bundle_id', 'email', 'firstname', 'lastname');
            if ($request->social_id) {
                $isUserExistWithSocialId = AppUserManager::baseQuery()->where(['social_id' => $request->social_id])->first();
                if (! $isUserExistWithSocialId) {
                    $data['social_id'] = $request->social_id;
                } else {
                    $newUser = AppUserManager::baseQuery()
                        ->where('ud_id', $request->ud_id)
                        ->whereNull('social_id')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($newUser) {
                        $newUser->forceDelete();
                    }
                    $isUserExistWithSocialId->update(['ud_id' => $udId]);
                }
            }
            $data['ip'] = $ipAddress;
            $model = AppUser::updateOrCreate(
                ['ud_id' => $udId],
                $data
            );
            $message = 'inserted';

            $user = AppUserManager::getByUdId($udId);
            $user->refresh();
            if ($request->social_id) {
                // if social id exist in database then generate token and update record based on current social id
                $tokenUser = AppUserManager::getBySocialId($request->social_id);
                if ($tokenUser) {
                    $user = $tokenUser;
                }
            }
            $token = $user->createToken('user_app_token')->accessToken;

            $user->access_token = $token;

            if (! $model->wasRecentlyCreated) {
                $message = 'updated';
            }
            if ($token) {

                $res = [
                    'user' => AppUserResource::make($user),
                ];

                return $this->responseJsonSuccess($res, $message);
            }
            $this->logOnError('Token Api Error', 'Token Not created', 401);

            return $this->responseInternalError();

        } catch (Exception $e) {
            $this->logOnError('Token Api Error', $e->getMessage(), $e->getCode());

            return $this->responseInternalError();
        }

    }
}
