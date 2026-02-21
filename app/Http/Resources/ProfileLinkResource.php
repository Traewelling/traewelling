<?php

namespace App\Http\Resources;

use App\Models\ProfileLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ProfileLinkResource",
 *     description="ProfileLinkResource",
 *     required={"name", "url"},
 *
 *     @OA\Property(property="name", type="enum", enum={"website", "instagram", "bluesky", "facebook", "mastodon", "tiktok", "github"}, example="website"),
 *     @OA\Property(property="url", type="string", example="https://traewelling.de"),
 * )
 */
class ProfileLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProfileLink $model */
        $model = $this->resource;

        return [
            'name' => $model->name->value,
            'url' => $model->url,
        ];
    }
}
