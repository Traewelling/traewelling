<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

class LightUserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'             => (int) $this->id,
            'displayName'    => (string) $this->name,
            'username'       => (string) $this->username,
            'profilePicture' => ProfilePictureController::getUrl($this->user),
            'mastodonUrl'    => $this->mastodonUrl ?? null,
            'preventIndex'   => (bool) $this->prevent_index
        ];
    }
}
