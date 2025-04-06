<?php

namespace App\Http\Resources;

use App\Enum\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array {
        return [
            'id'        => $this->id,
            'clientId'  => $this->oauth_client_id, // TODO: should be removed
            'client'    => new ClientResource($this->client),
            'userId'    => $this->user_id, // TODO: should be removed and replaced with user object
            'url'       => $this->url,
            'createdAt' => $this->created_at->toIso8601String(),
            'events'    => WebhookEventResource::collection($this->events),
        ];
    }
}
