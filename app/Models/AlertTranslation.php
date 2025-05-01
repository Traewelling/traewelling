<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string      $id
 * @property string      $banner_id
 * @property string      $locale
 * @property string      $content
 * @property string      $title
 * @property string|null $url
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @property-read Alert  $banner
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

    public function banner(): BelongsTo {
        return $this->belongsTo(Alert::class);
    }
}
