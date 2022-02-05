<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\User\ProfilePictureController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array {
        return [
            'username'      => $this->user->username,
            'profilePicture'=> ProfilePictureController::getUrl($this->user),
            'trainDuration' => (int) $this->duration,
            'trainDistance' => (float) $this->distance,
            'trainSpeed'    => (float) $this->speed,
            'points'        => (int) $this->points
        ];
    }
}
