<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->userAgent() ?? 'Unknown';

        $logMessage = sprintf(
            "[%s] %s | IP: %s | Auth User ID: %s | User-Agent: %s\nHeaders: %s\nBody: %s",
            $request->method(),
            $request->getRequestUri(),
            $request->ip(),
            optional($request->user())->id ?? 'null',
            $userAgent,
            json_encode($request->headers->all()),
            json_encode($request->all(), JSON_PRETTY_PRINT),
        );

        Log::info($logMessage);

        return $next($request);
    }
}
