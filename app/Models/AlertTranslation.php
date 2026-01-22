<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $alert_id
 * @property string $locale
 * @property string $title
 * @property string $content
 * @property string|null $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Alert|null $banner
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereUrl($value)
 *
 * @mixin \Eloquent
 */
class AlertTranslation extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'banner_id',
        'content',
        'title',
        'locale',
        'url',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }
}
