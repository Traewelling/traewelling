<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $provider
 * @property string|null $country
 * @property string|null $name
 * @property string|null $license
 * @property string|null $license_url
 * @property string|null $source_url
 * @property string|null $spdx
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $human_name
 * @property string|null $attribution_text
 * @property string|null $license_id
 * @property int $force_active
 * @property-read License|null $manualLicense
 * @property-read Collection<int, Trip> $trips
 * @property-read int|null $trips_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereForceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereHumanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereLicenseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereSpdx($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereUpdatedAt($value)
 *
 * @mixin \Eloquent
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
        'attribution_text',
        'sources',
        'license_url',
        'source_url',
        'spdx',
        'active',
        'force_active',
        'license_id',
    ];

    public const array SPDX = [
        'ODbL-1.0' => [
            'name' => 'Open Database License (ODbL)',
            'url' => 'https://spdx.org/licenses/ODbL-1.0.html',
        ],
        'CC-BY-4.0' => [
            'name' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
            'url' => 'https://spdx.org/licenses/CC-BY-4.0.html',
        ],
        'CC-BY-SA-4.0' => [
            'name' => 'Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)',
            'url' => 'https://spdx.org/licenses/CC-BY-SA-4.0.html',
        ],
        'CC0-1.0' => [
            'name' => 'Creative Commons Zero v1.0 Universal (CC0-1.0)',
            'url' => 'https://spdx.org/licenses/CC0-1.0.html',
        ],
        'CC-BY-1.0' => [
            'name' => 'Creative Commons Attribution 1.0 Generic (CC BY 1.0)',
            'url' => 'https://spdx.org/licenses/CC-BY-1.0.html',
        ],
        'CC-BY-2.5' => [
            'name' => 'Creative Commons Attribution 2.5 Generic (CC BY 2.5)',
            'url' => 'https://spdx.org/licenses/CC-BY-2.5.html',
        ],
        'CC-BY-3.0' => [
            'name' => 'Creative Commons Attribution 3.0 Unported (CC BY 3.0)',
            'url' => 'https://spdx.org/licenses/CC-BY-3.0.html',
        ],
        'etalab-2.0' => [
            'name' => 'Etalab Open License 2.0',
            'url' => 'https://spdx.org/licenses/etalab-2.0.html',
        ],
        'NLOD-1.0' => [
            'name' => 'Norwegian Licence for Open Government Data (NLOD) 1.0',
            'url' => 'https://spdx.org/licenses/NLOD-1.0.html',
        ],
        'OGL-UK-3.0' => [
            'name' => 'Open Government License v3.0 (UK)',
            'url' => 'https://spdx.org/licenses/OGL-UK-3.0.html',
            'attribution' => 'Contains public sector information licensed under the Open Government Licence v3.0, provided by :source.',
        ],
        'ODC-By-1.0' => [
            'name' => 'Open Data Commons Attribution License v1.0 (ODC-By-1.0)',
            'url' => 'https://spdx.org/licenses/ODC-By-1.0.html',
        ],
        'CC-BY-NC-4.0' => [
            'name' => 'Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)',
            'url' => 'https://spdx.org/licenses/CC-BY-NC-4.0.html',
        ],
        'CC-BY-ND-4.0' => [
            'name' => 'Creative Commons Attribution No Derivatives 4.0 International (CC-BY-ND-4.0)',
            'url' => 'https://spdx.org/licenses/CC-BY-ND-4.0.html',
        ],
        'MIT' => [
            'name' => 'MIT License',
            'url' => 'https://spdx.org/licenses/MIT.html',
        ],
        'OGL-ROU-1.0' => [
            'name' => 'Licența pentru o Guvernare Deschisă 1.0',
            'url' => 'https://data.gov.ro/base/images/logoinst/OGL-ROU-1.0.pdf',
            'attribution' => 'Conține informații publice în baza Licenței pentru Guvernare Deschisa v1.0',
        ],
        'CC-BY-SA-3.0' => [
            'name' => 'Creative Commons Attribution Share Alike 3.0 Unported',
            'url' => 'https://spdx.org/licenses/CC-BY-SA-3.0.html',
        ],
        'Unlicense' => [
            'name' => 'The Unlicense',
            'url' => 'https://spdx.org/licenses/Unlicense.html',
        ],
        'IODL-2.0' => [
            'name' => 'Italian Open Data Licence',
            'url' => 'https://www.dati.gov.it/iodl/2.0',
        ],
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function manualLicense(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id', 'id');
    }
}
