<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationName extends Model
{
    use HasUuids;

    protected $keyType      = 'string';
    public    $incrementing = false;
    protected $fillable     = ['station_id', 'language', 'name'];
    protected $casts        = [
        'station_id' => 'integer',
        'language'   => 'string',
        'name'       => 'string'
    ];

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
}
