<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $name
 * @property string|null $human_name
 * @property string|null $attribution
 * @property string|null $license_url
 * @property string|null $source_url
 * @property string|null $spdx
 * @property int $automatically_activate_source
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, MotisSourceLicense> $motisSourceLicenses
 * @property-read int|null $motis_source_licenses_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereAttribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereAutomaticallyActivateSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereHumanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereSpdx($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class License extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'name',
        'human_name',
        'attribution',
        'license_url',
        'source_url',
        'spdx',
        'automatically_activate_source',
    ];

    public $incrementing = false;

    public function motisSourceLicenses(): HasMany
    {
        return $this->hasMany(MotisSourceLicense::class);
    }
}
