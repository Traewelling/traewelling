<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $station_id
 * @property string $name
 * @property string|null $host
 * @property string|null $url
 * @property Carbon|null $begin
 * @property Carbon|null $end
 * @property bool $processed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $hashtag
 * @property int|null $telegram_notification_id
 * @property string|null $matrix_notification_id
 * @property-read Station|null $station
 * @property-read User|null $user
 *
 * @method static \Database\Factories\EventSuggestionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereAdminNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereBegin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereHashtag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereMatrixNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereTelegramNotificationId($value)
 *
 * @mixin \Eloquent
 */
class EventSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'host', 'url', 'station_id', 'begin', 'end', 'hashtag',
        'telegram_notification_id', 'matrix_notification_id', 'processed',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'station_id' => 'integer',
        'begin' => 'date',
        'end' => 'date',
        'hashtag' => 'string',
        'telegram_notification_id' => 'integer',
        'matrix_notification_id' => 'string',
        'processed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
}
