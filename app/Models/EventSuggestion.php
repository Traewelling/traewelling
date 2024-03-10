<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSuggestion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'host', 'url', 'station_id', 'begin', 'end', 'hashtag', 'processed'];
    protected $casts    = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'station_id' => 'integer',
        'begin'      => 'datetime',
        'end'        => 'datetime',
        'hashtag'    => 'string',
        'processed'  => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
}
