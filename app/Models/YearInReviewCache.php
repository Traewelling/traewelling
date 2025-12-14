<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YearInReviewCache extends Model
{
    use HasUuids;

    protected $table = 'year_in_review_cache';

    protected $fillable = [
        'user_id',
        'year',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
