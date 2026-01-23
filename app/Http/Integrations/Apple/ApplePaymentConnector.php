<?php

namespace App\Http\Integrations\Apple;

use App\Models\AppUser;
use App\Services\AppleApiCallService;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class ApplePaymentConnector extends Connector
{
    use AcceptsJson;

    public string $baseUrl;

    public string $token;

    public function __construct(public AppUser $user)
    {
        $this->baseUrl = AppleApiCallService::getSubscriptionBaseUrlFromEnvironment($user->environment);
        $this->token = AppleApiCallService::generateJWT();
    }

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
