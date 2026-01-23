<?php

namespace App\Http\Integrations\Bunny;

use Saloon\Http\Connector;

class BunnyStorageConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://storage.bunnycdn.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'AccessKey' => config('services.bunny.access_key'),
            'Content-Type' => 'text/plain',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 120,
        ];
    }
}
