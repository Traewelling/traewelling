<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MotisSource extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'provider',
        'country',
        'name',
        'license',
        'license_url',
        'source_url',
        'spdx',
        'active'
    ];
}
