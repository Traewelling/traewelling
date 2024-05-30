<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardUserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'username'       => $this->user->username,
            'profilePicture' => ProfilePictureController::getUrl($this->user),
            'trainDuration'  => (int) $this->duration, // @todo: rename key - we have more than just trains
            'trainDistance'  => (float) $this->distance, // @todo: rename key - we have more than just trains
            'points'         => (int) $this->points
        ];
    }
}
