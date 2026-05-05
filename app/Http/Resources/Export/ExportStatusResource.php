<?php

declare(strict_types=1);

namespace App\Http\Resources\Export;

use App\Dto\MentionDto;
use App\Http\Resources\ClientResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\LightUserResource;
use App\Http\Resources\StatusTagResource;
use App\Models\Status;
use App\Models\StatusTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class ExportStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Status $this */
        return [
            'id' => (int) $this->id,
            'body' => (string) $this->body,
            'bodyMentions' => $this->mentions->map(
                fn ($mention) => new MentionDto($mention->mentioned, $mention->position, $mention->length)
            ),
            'business' => (int) $this->business->value,
            'visibility' => (int) $this->visibility->value,
            'checkin' => new ExportCheckinResource($this->checkin),
            'client' => new ClientResource($this->client),
            'event' => new EventResource($this?->event),
            'user' => new LightUserResource($this->user),
            'createdBy' => $this->createdByUser
                ? new LightUserResource($this->createdByUser)
                : null,
            'tags' => StatusTagResource::collection(
                $this->tags->filter(fn (StatusTag $tag) => Gate::allows('view', $tag))
            ),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
