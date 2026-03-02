<?php

namespace App\Http\Requests;

use App\Enum\DataProvider;
use App\Enum\MapProvider;
use App\Enum\MastodonVisibility;
use App\Enum\ProfileLinkName;
use App\Enum\StatusVisibility;
use App\Enum\User\FriendCheckinSetting;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UpdateProfileInformationRequest',
    description: 'UpdateProfileInformationRequest',
    properties: [
        new OA\Property(property: 'username', type: 'string', example: 'gertrud123', maxLength: 25),
        new OA\Property(property: 'displayName', type: 'string', example: 'Gertrud', maxLength: 50),
        new OA\Property(property: 'privateProfile', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'preventIndex', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'privacyHideDays', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'defaultStatusVisibility', ref: '#/components/schemas/StatusVisibility', nullable: true),
        new OA\Property(property: 'mastodonVisibility', ref: '#/components/schemas/MastodonVisibility', nullable: true),
        new OA\Property(property: 'mapProvider', ref: '#/components/schemas/MapProvider', nullable: true),
        new OA\Property(property: 'friendCheckin', ref: '#/components/schemas/FriendCheckinSetting', nullable: true),
        new OA\Property(property: 'likesEnabled', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'pointsEnabled', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'bio', type: 'string', example: 'Hi there! I am Gertrud!', nullable: true, maxLength: 500),
        new OA\Property(property: 'experimental', description: 'Experimental features enabled', type: 'boolean', example: false),
        new OA\Property(property: 'profileLinks', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProfileLinkResource'), nullable: true),
        new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Berlin'),
    ],
)]
class UpdateProfileInformationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => [
                'required', 'string', 'max:25', 'regex:/^[a-zA-Z0-9_]*$/',
            ],
            'displayName' => ['required', 'string', 'max:50'],
            'privateProfile' => ['boolean', 'nullable'],
            'preventIndex' => ['boolean', 'nullable'],
            'privacyHideDays' => ['integer', 'nullable', 'gte:1'],
            'defaultStatusVisibility' => [
                'nullable',
                new Enum(StatusVisibility::class),
            ],
            'mastodonVisibility' => [
                'nullable',
                new Enum(MastodonVisibility::class),
            ],
            'mapProvider' => ['nullable', new Enum(MapProvider::class)],
            'dataProvider' => ['nullable', new Enum(DataProvider::class)],
            'friendCheckin' => ['nullable', new Enum(FriendCheckinSetting::class)],
            'likesEnabled' => ['nullable', 'boolean'],
            'pointsEnabled' => ['nullable', 'boolean'],
            'bio' => ['nullable', 'string', 'max:500'],
            'experimental' => ['boolean', 'nullable'],
            'profileLinks.*.name' => ['required', 'string', new Enum(ProfileLinkName::class)],
            'profileLinks.*.url' => ['required', 'string', 'url', 'max:255'],
            'timezone' => ['string', Rule::in(DateTimeZone::listIdentifiers())],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        ];
    }
}
