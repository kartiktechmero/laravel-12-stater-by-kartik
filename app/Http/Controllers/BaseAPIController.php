<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BaseAPIController extends Controller
{
    public function responseJsonSuccess(mixed $data = [], string $msg = 'Success', array $extraData = []): JsonResponse
    {
        $response = ['status' => true, 'message' => $msg, 'result' => $data];
        if (! empty($extraData)) {
            $response = array_merge($response, $extraData);
        }

        return response()->json($response);
    }

    public function responseJsonError(mixed $data = [], string $msg = 'Validation Error'): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $msg,
            'validation_errors' => $data,
        ]);
    }

    public function responseInternalError(string $msg = 'Something went to wrong'): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $msg,
        ]);
    }

    public function logOnError(string $logOn, string $message, int|string $code, ?int $line = null): void
    {
        Log::info($logOn.': '.$message.' code:'.$code.' Line:'.$line);
    }

    public function validationResponseError(array $errorsArray): array
    {
        $responseErrors = [];
        foreach ($errorsArray as $Key => $value) {
            $responseErrors[] = $value[0];
        }

        return $responseErrors;
    }
}
