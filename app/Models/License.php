<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class License
 *
 * @property string  $id
 * @property ?string $name
 * @property ?string $human_name
 * @property ?string $attribution
 * @property ?string $license_url
 * @property ?string $source_url
 * @property ?string $spdx
 * @property bool    $automatically_activate_source
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
        'automatically_activate_source'
    ];

    public $incrementing = false;

    public function motisSourceLicenses(): HasMany {
        return $this->hasMany(MotisSourceLicense::class);
    }
}
