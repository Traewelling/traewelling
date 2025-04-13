<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string       $id
 * @property ?string      $provider
 * @property ?string      $country
 * @property ?string      $name
 * @property ?string      $human_name
 * @property ?string      $sources
 * @property ?string      $license_url
 * @property ?string      $source_url
 * @property ?string      $spdx
 * @property bool         $active
 * @property bool         $force_active
 *
 * --- Relations
 * @property-read License $otherLicense
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
    ];

    public function trips(): HasMany {
        return $this->hasMany(Trip::class);
    }

    public function otherLicense(): BelongsTo {
        return $this->belongsTo(License::class);
    }
}
