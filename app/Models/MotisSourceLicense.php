<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string $id
 * @property string|null $provider
 * @property string|null $country
 * @property string|null $name
 * @property string|null $human_name
 * @property string|null $license
 * @property string|null $license_url
 * @property string|null $source_url
 * @property string|null $spdx
 * @property string|null $license_id
 * @property int $active
 * @property int $force_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\License|null $manualLicense
 * @property-read Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 * @method static Builder<static>|MotisSourceLicense newModelQuery()
 * @method static Builder<static>|MotisSourceLicense newQuery()
 * @method static Builder<static>|MotisSourceLicense query()
 * @method static Builder<static>|MotisSourceLicense whereActive($value)
 * @method static Builder<static>|MotisSourceLicense whereCountry($value)
 * @method static Builder<static>|MotisSourceLicense whereCreatedAt($value)
 * @method static Builder<static>|MotisSourceLicense whereForceActive($value)
 * @method static Builder<static>|MotisSourceLicense whereHumanName($value)
 * @method static Builder<static>|MotisSourceLicense whereId($value)
 * @method static Builder<static>|MotisSourceLicense whereLicense($value)
 * @method static Builder<static>|MotisSourceLicense whereLicenseId($value)
 * @method static Builder<static>|MotisSourceLicense whereLicenseUrl($value)
 * @method static Builder<static>|MotisSourceLicense whereName($value)
 * @method static Builder<static>|MotisSourceLicense whereProvider($value)
 * @method static Builder<static>|MotisSourceLicense whereSourceUrl($value)
 * @method static Builder<static>|MotisSourceLicense whereSpdx($value)
 * @method static Builder<static>|MotisSourceLicense whereUpdatedAt($value)
 * @mixin Eloquent
 */
class MotisSourceLicense extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'provider',
        'country',
        'name',
        'human_name',
        'sources',
        'license_url',
        'source_url',
        'spdx',
        'active',
        'force_active',
    ];

    public const array SPDX = [
        'ODbL-1.0'     => [
            'name' => 'Open Database License (ODbL)',
            'url'  => 'https://spdx.org/licenses/ODbL-1.0.html'
        ],
        'CC-BY-4.0'    => [
            'name' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
            'url'  => 'https://spdx.org/licenses/CC-BY-4.0.html'
        ],
        'CC-BY-SA-4.0' => [
            'name' => 'Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)',
            'url'  => 'https://spdx.org/licenses/CC-BY-SA-4.0.html'
        ],
        'CC0-1.0'      => [
            'name' => 'Creative Commons Zero v1.0 Universal (CC0-1.0)',
            'url'  => 'https://spdx.org/licenses/CC0-1.0.html'
        ],
        'CC-BY-1.0'    => [
            'name' => 'Creative Commons Attribution 1.0 Generic (CC BY 1.0)',
            'url'  => 'https://spdx.org/licenses/CC-BY-1.0.html'
        ],
        'CC-BY-2.5'    => [
            'name' => 'Creative Commons Attribution 2.5 Generic (CC BY 2.5)',
            'url'  => 'https://spdx.org/licenses/CC-BY-2.5.html'
        ],
        'CC-BY-3.0'    => [
            'name' => 'Creative Commons Attribution 3.0 Unported (CC BY 3.0)',
            'url'  => 'https://spdx.org/licenses/CC-BY-3.0.html'
        ],
        'etalab-2.0'   => [
            'name' => 'Etalab Open License 2.0',
            'url'  => 'https://spdx.org/licenses/etalab-2.0.html'
        ],
        'NLOD-1.0'     => [
            'name' => 'Norwegian Licence for Open Government Data (NLOD) 1.0',
            'url'  => 'https://spdx.org/licenses/NLOD-1.0.html'
        ],
        'OGL-UK-3.0'   => [
            'name'        => 'Open Government License v3.0 (UK)',
            'url'         => 'https://spdx.org/licenses/OGL-UK-3.0.html',
            'attribution' => 'Contains public sector information licensed under the Open Government Licence v3.0, provided by :source.',
        ],
        'ODC-By-1.0'   => [
            'name' => 'Open Data Commons Attribution License v1.0 (ODC-By-1.0)',
            'url'  => 'https://spdx.org/licenses/ODC-By-1.0.html',
        ],
        'CC-BY-NC-4.0' => [
            'name' => 'Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)',
            'url'  => 'https://spdx.org/licenses/CC-BY-NC-4.0.html'
        ]
    ];

    public function trips(): HasMany {
        return $this->hasMany(Trip::class);
    }

    public function manualLicense(): BelongsTo {
        return $this->belongsTo(License::class, 'license_id', 'id');
    }
}
