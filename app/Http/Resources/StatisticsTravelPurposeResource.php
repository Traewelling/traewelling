<?php

namespace App\Http\Resources;

use App\Enum\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsTravelPurposeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        if ($this->reason == Business::PRIVATE) {
            $this->reason = 'private';
        } elseif ($this->reason == Business::BUSINESS) {
            $this->reason = 'business';
        } elseif ($this->reason == Business::COMMUTE) {
            $this->reason = 'commute';
        }

        return [
            'name'     => $this->reason,
            'count'    => $this->count,
            'duration' => $this->duration
        ];
    }
}
