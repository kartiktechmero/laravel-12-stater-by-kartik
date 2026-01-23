<?php

namespace App\Http\Integrations\Bunny\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStreamBody;

class BunnyStorageUploadVocabularyImageRequest extends Request implements HasBody
{
    use HasStreamBody;

    protected Method $method = Method::PUT;

    protected string $storageZone;

    protected string $remotePath;

    protected string $localFilePath;

    /**
     * @param  string  $remotePath  Path inside storage (file name included)
     * @param  string  $localFilePath  Absolute local file path
     */
    public function __construct(
        string $remotePath,
        string $localFilePath
    ) {
        $this->storageZone = config('services.bunny.storage_zone');
        $this->remotePath = $remotePath;
        $this->localFilePath = $localFilePath;
    }

    /**
     * Endpoint matches curl URL:
     * https://HOSTNAME/{zone}/{file}
     */
    public function resolveEndpoint(): string
    {
        return "/{$this->storageZone}/{$this->remotePath}";
    }

    protected function defaultBody(): mixed
    {
        return fopen($this->localFilePath, 'r');
    }
}
