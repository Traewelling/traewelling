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
    ];
}
