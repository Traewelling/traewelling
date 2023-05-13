<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
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
            'username'                => $this->username,
            'displayName'             => $this->name,
            'profilePicture'          => ProfilePictureController::getUrlForUserId($this->id),
            'privateProfile'          => (bool) $this->private_profile,
            'preventIndex'            => (bool) $this->prevent_index,
            'defaultStatusVisibility' => (int) $this->default_status_visibility->value,
            'privacyHideDays'         => (int) $this->privacy_hide_days->value,
            'password'                => (bool) $this->password,
            'email'                   => $this->email,
            'emailVerified'           => !empty($this->email_verified_at),
            'profilePictureSet'       => !empty($this->avatar),
            'twitter'                 => null, //deprecated
            'mastodon'                => $this->mastodon_url,
            'mastodonVisibility'      => $this->socialProfile->mastodon_visibility->value,
        ];
    }
}
