<?php

use App\Http\Middleware\LogRequestHeaders;
use App\Http\Middleware\VerifySignature;
use App\Mail\SystemMail\CustomExceptionOccurredMail;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verify.signature' => VerifySignature::class,
        ]);
        $middleware->group('web', [
            LogRequestHeaders::class,
        ]);

        $middleware->group('api', [
            LogRequestHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            if (app()->isProduction() && config('services.mail_enable')) {
                try {
                    $request = request();
                    $user = $request->user() ?? null;

                    Illuminate\Support\Facades\Mail::to(config('services.exception_mail'))
                        ->send(new CustomExceptionOccurredMail($e, $user));
                } catch (Exception $ex) {
                    Log::error('Could not send exception email: '.$ex->getMessage());
                }
            }
        });

        $exceptions->renderable(function (ThrottleRequestsException $e) {
            return response()->json(['error' => 'Too Many Attempts. Please try again later.'], 429);
        });
    })
    ->create();
