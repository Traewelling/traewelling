<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * // properties
 * @property int    $id
 * @property int    $user_id
 * @property string $name
 * @property string $host
 * @property string $url
 * @property int    $station_id
 * @property Carbon $begin
 * @property Carbon $end
 * @property string $hashtag
 * @property int    $admin_notification_id
 * @property bool   $processed
 */
class EventSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'host', 'url', 'station_id', 'begin', 'end', 'hashtag',
        'admin_notification_id', 'processed'
    ];
    protected $casts    = [
        'id'                    => 'integer',
        'user_id'               => 'integer',
        'station_id'            => 'integer',
        'begin'                 => 'datetime',
        'end'                   => 'datetime',
        'hashtag'               => 'string',
        'admin_notification_id' => 'integer',
        'processed'             => 'boolean',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
}
