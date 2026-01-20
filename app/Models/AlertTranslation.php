<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
