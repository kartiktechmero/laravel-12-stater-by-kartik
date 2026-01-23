<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySignature
{
    private const SECRET_KEY = '87b1c31c076ba5642233f1e0f2a0eff6';

    public function handle(Request $request, Closure $next): Response
    {
        $verifyHeader = $request->header('verify');

        if (! $verifyHeader) {
            return response()->json(['error' => 'Missing verify header'], 401);
        }

        // Decode from base64
        $decodedToken = base64_decode($verifyHeader, true);

        if (! $decodedToken || mb_strpos($decodedToken, '-') === false) {
            return response()->json(['error' => 'Invalid token format'], 401);
        }

        [$timestamp, $signature] = explode('-', $decodedToken, 2);

        if (! is_numeric($timestamp) || abs(time() - (int) $timestamp) > 10000000) {
            return response()->json(['error' => 'Timestamp expired or invalid'], 401);
        }

        // Extract only the path after /api/
        $fullPath = $request->getPathInfo(); // e.g. /path/api/test
        // $apiIndex = strpos($fullPath, '/api');
        // $pathAfterApi = ($apiIndex !== false) ? substr($fullPath, $apiIndex + 4) : '';

        $dataToAuthenticate = $fullPath.$timestamp;

        $computedSignature = base64_encode(
            hash_hmac('sha256', $dataToAuthenticate, self::SECRET_KEY, true)
        );

        if (! hash_equals($computedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
