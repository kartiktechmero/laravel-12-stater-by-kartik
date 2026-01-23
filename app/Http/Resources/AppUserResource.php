<?php

namespace App\Http\Resources;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AppUser
 */
class AppUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'user_id' => $this->id,
            'ud_id' => $this->ud_id,
            'social_id' => $this->social_id,
            'current_app_version' => $this->current_app_version,
            'token_id' => $this->token_id,
            'email' => $this->email,
            'name' => $this->name,
            'environment' => $this->environment,
            'bundle_id' => $this->bundle_id,
            'access_token' => $this->access_token,
        ];

    }
}
