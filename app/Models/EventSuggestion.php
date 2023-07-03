<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, false $false)
 */
class EventSuggestion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'host', 'url', 'train_station_id', 'begin', 'end', 'processed'];
    protected $casts    = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'train_station_id' => 'integer',
        'begin'            => 'datetime',
        'end'              => 'datetime',
        'processed'        => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function trainStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'train_station_id', 'id');
    }
}
