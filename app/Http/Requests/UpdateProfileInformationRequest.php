<?php

namespace App\Http\Requests;

use App\Enum\DataProvider;
use App\Enum\MapProvider;
use App\Enum\MastodonVisibility;
use App\Enum\ProfileLinkName;
use App\Enum\StatusVisibility;
use App\Enum\User\FriendCheckinSetting;
use App\Http\Resources\ProfileLinkResource;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UpdateProfileInformationRequest',
    description: 'All fields are optional. Only send what you want to change.',
    properties: [
        new OA\Property(property: 'username', type: 'string', example: 'gertrud123', maxLength: 25),
        new OA\Property(property: 'displayName', type: 'string', example: 'Gertrud', maxLength: 50),
        new OA\Property(property: 'privateProfile', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'preventIndex', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'privacyHideDays', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'defaultStatusVisibility', ref: StatusVisibility::class, nullable: true),
        new OA\Property(property: 'mastodonVisibility', ref: MastodonVisibility::class, nullable: true),
        new OA\Property(property: 'mapProvider', ref: MapProvider::class, nullable: true),
        new OA\Property(property: 'friendCheckin', ref: FriendCheckinSetting::class, nullable: true),
        new OA\Property(property: 'likesEnabled', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'pointsEnabled', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'bio', type: 'string', example: 'Hi there! I am Gertrud!', nullable: true, maxLength: 500),
        new OA\Property(property: 'experimental', description: 'Experimental features enabled', type: 'boolean', example: false),
        new OA\Property(property: 'profileLinks', type: 'array', items: new OA\Items(ref: ProfileLinkResource::class), nullable: true),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Berlin'),
    ],
)]
class UpdateProfileInformationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => [
                'sometimes', 'string', 'max:25', 'regex:/^[a-zA-Z0-9_]*$/',
            ],
            'displayName' => ['sometimes', 'string', 'max:50'],
            'privateProfile' => ['sometimes', 'boolean', 'nullable'],
            'preventIndex' => ['sometimes', 'boolean', 'nullable'],
            'privacyHideDays' => ['sometimes', 'integer', 'nullable', 'gte:1'],
            'defaultStatusVisibility' => [
                'sometimes',
                'nullable',
                new Enum(StatusVisibility::class),
            ],
            'mastodonVisibility' => [
                'sometimes',
                'nullable',
                new Enum(MastodonVisibility::class),
            ],
            'mapProvider' => ['sometimes', 'nullable', new Enum(MapProvider::class)],
            'dataProvider' => ['sometimes', 'nullable', new Enum(DataProvider::class)],
            'friendCheckin' => ['sometimes', 'nullable', new Enum(FriendCheckinSetting::class)],
            'likesEnabled' => ['sometimes', 'nullable', 'boolean'],
            'pointsEnabled' => ['sometimes', 'nullable', 'boolean'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:500'],
            'experimental' => ['sometimes', 'boolean', 'nullable'],
            'profileLinks.*.name' => ['required_with:profileLinks', 'string', new Enum(ProfileLinkName::class)],
            'profileLinks.*.url' => ['required_with:profileLinks', 'string', 'url', 'max:255'],
            'timezone' => ['sometimes', 'string', Rule::in(DateTimeZone::listIdentifiers())],
        ];
    }
}
