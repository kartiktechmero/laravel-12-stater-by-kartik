<?php

declare(strict_types=1);

namespace App\Mail\SystemMail;

use App\Models\AppUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;
use Throwable;

class CustomExceptionOccurredMail extends Mailable
{
    use Queueable, SerializesModels;

    public Throwable $exception;

    public AppUser|User|null $user;

    public function __construct(Throwable $exception, AppUser|User|null $user)
    {
        $this->exception = $exception;
        $this->user = $user;
    }

    public function build(): self
    {
        $request = request();

        $appName = config('app.name');
        $env = app()->environment();
        $userId = $this->user->id ?? 'guest';
        $userEmail = $this->user->email ?? 'N/A';
        $url = $request->fullUrl();
        $method = $request->method();
        $exceptionType = (new ReflectionClass($this->exception))->getShortName();

        // Compose a super-informative subject
        $subject = "[{$appName}][{$env}][{$exceptionType}] User:{$userId} ({$userEmail}) {$method} {$url}";

        return $this->subject($subject)
            ->view('emails.log.exception-occurred')
            ->with([
                'subject' => $subject,
                'mail_message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'trace' => $this->exception->getTraceAsString(),
                'url' => $url,
                'method' => $method,
                'input' => json_encode($request->except(['password', 'password_confirmation']), JSON_PRETTY_PRINT),
                'ip' => $request->header('cf-connecting-ip') ?? $request->header('client-ip') ?? $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $userId,
                'user_email' => $userEmail,
                'app_env' => $env,
                'php_version' => PHP_VERSION,
            ]);
    }
}
