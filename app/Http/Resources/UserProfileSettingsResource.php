<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserProfileSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request) {
        return [
            'username'                  => $this->username,
            'name'                      => $this->name,
            'private_profile'           => (bool) $this->private_profile,
            'prevent_index'             => (bool) $this->prevent_index,
            'always_dbl'                => (bool) $this->always_dbl,
            'default_status_visibility' => (int) $this->default_status_visibility,
            'password'                  => (bool) $this->password,
            'email'                     => $this->email,
            'email_verified'            => !empty($this->email_verified_at),
            'profile_picture_set'       => !empty($this->avatar),
            'twitter'                   => $this->twitter_url,
            'mastodon'                  => $this->mastodon_url
        ];
    }
}
