<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'automatically_activate_source' => 'boolean',
    ];

    public $incrementing = false;

    public function motisSourceLicenses(): HasMany
    {
        return $this->hasMany(MotisSourceLicense::class);
    }
}
