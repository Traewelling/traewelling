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
        new OA\Property(property: 'username', type: 'string', maxLength: 25, example: 'gertrud123'),
        new OA\Property(property: 'displayName', type: 'string', maxLength: 50, example: 'Gertrud'),
        new OA\Property(property: 'privateProfile', type: 'boolean', nullable: true, example: false),
        new OA\Property(property: 'preventIndex', type: 'boolean', nullable: true, example: false),
        new OA\Property(property: 'privacyHideDays', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'defaultStatusVisibility', ref: '#/components/schemas/StatusVisibility', nullable: true),
        new OA\Property(property: 'mastodonVisibility', ref: '#/components/schemas/MastodonVisibility', nullable: true),
        new OA\Property(property: 'mapProvider', ref: '#/components/schemas/MapProvider', nullable: true),
        new OA\Property(property: 'friendCheckin', ref: '#/components/schemas/FriendCheckinSetting', nullable: true),
        new OA\Property(property: 'likesEnabled', type: 'boolean', nullable: true, example: true),
        new OA\Property(property: 'pointsEnabled', type: 'boolean', nullable: true, example: true),
        new OA\Property(property: 'bio', type: 'string', maxLength: 500, nullable: true, example: 'Hi there! I am Gertrud!'),
        new OA\Property(property: 'experimental', type: 'boolean', example: false, description: 'Experimental features enabled'),
        new OA\Property(property: 'profileLinks', type: 'array', nullable: true, items: new OA\Items(ref: '#/components/schemas/ProfileLinkResource')),
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
