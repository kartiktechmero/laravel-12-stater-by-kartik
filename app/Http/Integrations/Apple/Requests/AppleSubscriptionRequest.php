<?php

namespace App\Http\Integrations\Apple\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class AppleSubscriptionRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public string $resolve_endpoint;

    public function __construct(public string $subscriptionId)
    {
        $this->resolve_endpoint = '/inApps/v1/subscriptions/'.$subscriptionId;
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return $this->resolve_endpoint;
    }
}
